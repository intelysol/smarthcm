<?php

namespace Tests\Feature\SelfService;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\BusinessUnit;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Department;
use App\Domains\SelfService\Enums\ServiceRequestStatus;
use App\Domains\SelfService\Models\HrServiceCategory;
use App\Domains\SelfService\Models\HrServiceDefinition;
use App\Domains\SelfService\Models\HrServiceFormDefinition;
use App\Domains\SelfService\Models\HrServiceVersion;
use App\Domains\SelfService\Services\ServiceRequestService;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceRequestLifecycleAndHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_request_lifecycle_and_status_history(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $bu = BusinessUnit::create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'name' => 'HQ Unit', 'code' => 'BU-HQ']);
        $department = Department::create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'business_unit_id' => $bu->id, 'department_name' => 'HR', 'department_code' => 'HR-DEPT']);
        
        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'department_id' => $department->id,
            'employee_code' => 'EMP-100',
            'employee_number' => 'EMP-100',
            'first_name' => 'Alice',
            'last_name' => 'Walker',
            'official_email' => 'alice@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ]);

        $actor = User::factory()->create(['tenant_id' => $tenant->id]);

        $category = HrServiceCategory::create([
            'tenant_id' => $tenant->id,
            'code' => 'CAT-HR',
            'name' => 'General HR',
        ]);

        $service = HrServiceDefinition::create([
            'tenant_id' => $tenant->id,
            'hr_service_category_id' => $category->id,
            'service_code' => 'SVC-QUERY',
            'name' => 'HR General Inquiry',
            'allow_reopen' => true,
            'reopen_window_days' => 14,
        ]);

        $version = HrServiceVersion::create([
            'tenant_id' => $tenant->id,
            'hr_service_definition_id' => $service->id,
            'version_number' => 1,
            'effective_from' => '2026-01-01',
        ]);

        HrServiceFormDefinition::create([
            'tenant_id' => $tenant->id,
            'hr_service_version_id' => $version->id,
            'form_name' => 'Inquiry Form',
            'schema' => [
                ['key' => 'question_details', 'label' => 'Details', 'type' => 'textarea', 'required' => true],
            ],
        ]);

        $requestService = app(ServiceRequestService::class);

        // 1. Create Draft Request with dynamic fields
        $request = $requestService->createRequest(
            $employee,
            $service,
            [
                'subject' => 'Inquiry on Health Insurance Coverage',
                'description' => 'Need details regarding dental coverage.',
                'form_data' => [
                    'question_details' => 'Does the basic insurance plan cover dental cleanings?',
                ],
            ],
            $actor
        );

        $this->assertEquals(ServiceRequestStatus::DRAFT->value, $request->status);
        $this->assertEquals($department->id, $request->department_id);
        $this->assertEquals($company->id, $request->company_id);
        $this->assertCount(1, $request->fields);
        $this->assertEquals('question_details', $request->fields->first()->field_key);

        // 2. Submit Request
        $submitted = $requestService->submitRequest($request, $actor);
        $this->assertNotEquals(ServiceRequestStatus::DRAFT->value, $submitted->status);

        // 3. Status Transitions
        $inProgress = $requestService->transitionStatus($submitted, ServiceRequestStatus::IN_PROGRESS->value, $actor, 'Agent started processing');
        $this->assertEquals(ServiceRequestStatus::IN_PROGRESS->value, $inProgress->status);

        $resolved = $requestService->transitionStatus($inProgress, ServiceRequestStatus::RESOLVED->value, $actor, 'Provided dental policy handbook');
        $this->assertEquals(ServiceRequestStatus::RESOLVED->value, $resolved->status);
        $this->assertNotNull($resolved->resolved_at);

        // 4. Reopen Request
        $reopened = $requestService->reopenRequest($resolved, $actor, 'Had a follow-up question regarding dependents');
        $this->assertEquals(ServiceRequestStatus::REOPENED->value, $reopened->status);
        $this->assertNull($reopened->resolved_at);

        // 5. Verify audit history entries
        $this->assertGreaterThanOrEqual(4, $reopened->statusHistory()->count());
    }
}
