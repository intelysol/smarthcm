<?php

namespace Tests\Feature\SharedServices;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\SelfService\Enums\ServiceRequestStatus;
use App\Domains\SelfService\Models\HrServiceCategory;
use App\Domains\SelfService\Models\HrServiceDefinition;
use App\Domains\SelfService\Models\HrServiceRequest;
use App\Domains\SelfService\Models\HrServiceRequestComment;
use App\Domains\SelfService\Models\HrServiceRequestLink;
use App\Domains\SelfService\Services\ServiceDuplicateDetectionService;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceDuplicateDetectionAndMergeTest extends TestCase
{
    use RefreshDatabase;

    public function test_duplicate_detection_and_safe_merge(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $agent = User::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-301',
            'employee_number' => 'EMP-301',
            'first_name' => 'Charlie',
            'last_name' => 'Brown',
            'official_email' => 'charlie@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ]);

        $category = HrServiceCategory::create([
            'tenant_id' => $tenant->id,
            'code' => 'CAT_PAYROLL',
            'name' => 'Payroll',
            'is_active' => true,
        ]);

        $service = HrServiceDefinition::create([
            'tenant_id' => $tenant->id,
            'hr_service_category_id' => $category->id,
            'service_code' => 'SVC-PAYSLIP',
            'name' => 'Missing Payslip Inquiry',
            'status' => 'active',
        ]);

        $primaryRequest = HrServiceRequest::create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'hr_service_definition_id' => $service->id,
            'request_number' => 'REQ-202609-0010',
            'subject' => 'Missing August payslip in portal',
            'description' => 'I cannot see my payslip for August.',
            'status' => ServiceRequestStatus::IN_PROGRESS->value,
            'created_at' => now()->subDay(),
        ]);

        $secondaryRequest = HrServiceRequest::create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'hr_service_definition_id' => $service->id,
            'request_number' => 'REQ-202609-0011',
            'subject' => 'Missing August payslip follow-up',
            'description' => 'Still need my August payslip.',
            'status' => ServiceRequestStatus::SUBMITTED->value,
            'created_at' => now(),
        ]);

        $duplicateService = app(ServiceDuplicateDetectionService::class);

        // 1. Detect duplicates
        $duplicates = $duplicateService->findPotentialDuplicates($secondaryRequest);
        $this->assertNotEmpty($duplicates);
        $this->assertTrue($duplicates->contains('id', $primaryRequest->id));

        // 2. Perform safe merge
        $result = $duplicateService->mergeRequests($primaryRequest, $secondaryRequest, $agent, 'Merged duplicate ticket by employee');

        $this->assertEquals('successfully_merged', $result['status']);
        $this->assertEquals($primaryRequest->id, $result['primary_request_id']);

        // 3. Verify secondary is closed
        $secondaryRequest->refresh();
        $this->assertEquals(ServiceRequestStatus::CLOSED->value, $secondaryRequest->status);

        // 4. Verify link created
        $this->assertDatabaseHas('hr_service_request_links', [
            'tenant_id' => $tenant->id,
            'parent_request_id' => $primaryRequest->id,
            'child_request_id' => $secondaryRequest->id,
            'link_type' => 'merged_into',
        ]);

        // 5. Verify audit comments on both
        $this->assertDatabaseHas('hr_service_request_comments', [
            'tenant_id' => $tenant->id,
            'hr_service_request_id' => $primaryRequest->id,
            'comment_type' => 'internal',
        ]);

        $this->assertDatabaseHas('hr_service_request_comments', [
            'tenant_id' => $tenant->id,
            'hr_service_request_id' => $secondaryRequest->id,
            'comment_type' => 'public',
        ]);
    }
}
