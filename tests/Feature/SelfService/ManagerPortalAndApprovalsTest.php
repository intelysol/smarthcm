<?php

namespace Tests\Feature\SelfService;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\SelfService\Enums\ServiceRequestStatus;
use App\Domains\SelfService\Models\HrServiceCategory;
use App\Domains\SelfService\Models\HrServiceDefinition;
use App\Domains\SelfService\Models\HrServiceVersion;
use App\Domains\SelfService\Services\ManagerPortalService;
use App\Domains\SelfService\Services\ServiceRequestService;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManagerPortalAndApprovalsTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_portal_team_roster_and_pending_approvals(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $manager = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'MGR-01',
            'employee_number' => 'MGR-01',
            'first_name' => 'Ian',
            'last_name' => 'McKellen',
            'official_email' => 'ian@example.com',
            'employment_status' => 'active',
            'joining_date' => '2025-01-01',
        ]);

        $report1 = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'reporting_manager_id' => $manager->id,
            'employee_code' => 'REP-01',
            'employee_number' => 'REP-01',
            'first_name' => 'John',
            'last_name' => 'Watson',
            'official_email' => 'john@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ]);

        $report2 = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'reporting_manager_id' => $manager->id,
            'employee_code' => 'REP-02',
            'employee_number' => 'REP-02',
            'first_name' => 'Jane',
            'last_name' => 'Eyre',
            'official_email' => 'jane@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ]);

        $category = HrServiceCategory::create(['tenant_id' => $tenant->id, 'code' => 'CAT-HR', 'name' => 'HR Operations']);
        $service = HrServiceDefinition::create(['tenant_id' => $tenant->id, 'hr_service_category_id' => $category->id, 'service_code' => 'SVC-APPROVAL', 'name' => 'Tuition Reimbursement', 'requires_approval' => true]);
        $version = HrServiceVersion::create(['tenant_id' => $tenant->id, 'hr_service_definition_id' => $service->id, 'version_number' => 1, 'effective_from' => '2026-01-01']);

        $requestService = app(ServiceRequestService::class);
        $managerService = app(ManagerPortalService::class);

        // Report 1 creates request needing approval
        $req1 = $requestService->createRequest($report1, $service, ['subject' => 'Certification Course']);
        $submitted1 = $requestService->submitRequest($req1);
        $submitted1->update(['status' => ServiceRequestStatus::WAITING_FOR_APPROVAL->value]);

        $dashboard = $managerService->getManagerDashboard($manager);

        $this->assertEquals(2, $dashboard['team_size']);
        $this->assertCount(2, $dashboard['team_roster']);
        $this->assertEquals(1, $dashboard['pending_service_approvals_count']);
    }
}
