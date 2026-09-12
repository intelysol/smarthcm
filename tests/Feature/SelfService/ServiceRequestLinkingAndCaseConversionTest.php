<?php

namespace Tests\Feature\SelfService;

use App\Domains\Employee\Models\Employee;
use App\Domains\EmployeeRelations\Models\EmployeeRelationCase;
use App\Domains\Organization\Models\Company;
use App\Domains\SelfService\Enums\ServiceLinkType;
use App\Domains\SelfService\Enums\ServiceRequestStatus;
use App\Domains\SelfService\Models\HrServiceCategory;
use App\Domains\SelfService\Models\HrServiceDefinition;
use App\Domains\SelfService\Models\HrServiceVersion;
use App\Domains\SelfService\Services\ServiceRequestService;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceRequestLinkingAndCaseConversionTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_request_linking_merging_and_case_conversion(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-800',
            'employee_number' => 'EMP-800',
            'first_name' => 'Kevin',
            'last_name' => 'Spacey',
            'official_email' => 'kevin@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ]);

        $actor = User::factory()->create(['tenant_id' => $tenant->id]);

        $category = HrServiceCategory::create(['tenant_id' => $tenant->id, 'code' => 'CAT-HR', 'name' => 'HR']);
        $service = HrServiceDefinition::create(['tenant_id' => $tenant->id, 'hr_service_category_id' => $category->id, 'service_code' => 'SVC-COMPLAINT', 'name' => 'Workplace Complaint']);
        $version = HrServiceVersion::create(['tenant_id' => $tenant->id, 'hr_service_definition_id' => $service->id, 'version_number' => 1, 'effective_from' => '2026-01-01']);

        $requestService = app(ServiceRequestService::class);

        // 1. Create two requests and merge them
        $master = $requestService->createRequest($employee, $service, ['subject' => 'Harassment complaint']);
        $duplicate = $requestService->createRequest($employee, $service, ['subject' => 'Follow up on harassment incident']);

        $mergedMaster = $requestService->mergeRequests($master, $duplicate, $actor, 'Duplicate ticket filed by user');
        $this->assertEquals(ServiceRequestStatus::CLOSED->value, $duplicate->fresh()->status);
        $this->assertCount(1, $master->childLinks);
        $this->assertEquals(ServiceLinkType::DUPLICATED_BY->value, $master->childLinks->first()->link_type);

        // 2. Convert master service request to formal Employee Relations Case
        $erCase = $requestService->convertToHrCase($mergedMaster, $actor, [
            'title' => 'Formal Investigation: Harassment Complaint',
            'description' => 'Escalated from HR Service Desk ticket ' . $mergedMaster->request_number,
            'severity' => 'high',
        ]);

        $this->assertInstanceOf(EmployeeRelationCase::class, $erCase);
        $this->assertEquals($erCase->id, $mergedMaster->fresh()->employee_relation_case_id);
        $this->assertEquals(ServiceRequestStatus::RESOLVED->value, $mergedMaster->fresh()->status);
    }
}
