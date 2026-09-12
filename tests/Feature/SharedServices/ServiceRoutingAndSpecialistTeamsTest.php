<?php

namespace Tests\Feature\SharedServices;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\SelfService\Models\HrServiceCategory;
use App\Domains\SelfService\Models\HrServiceDefinition;
use App\Domains\SelfService\Models\HrServiceQueue;
use App\Domains\SelfService\Models\HrServiceTeam;
use App\Domains\SelfService\Models\HrServiceTeamMember;
use App\Domains\SelfService\Services\ServiceRoutingService;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceRoutingAndSpecialistTeamsTest extends TestCase
{
    use RefreshDatabase;

    public function test_intelligent_routing_to_payroll_and_sensitive_er_queues(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-101',
            'employee_number' => 'EMP-101',
            'first_name' => 'Alice',
            'last_name' => 'Smith',
            'official_email' => 'alice@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ]);

        $payrollQueue = HrServiceQueue::create([
            'tenant_id' => $tenant->id,
            'code' => 'PAYROLL_SUPPORT_QUEUE',
            'name' => 'Payroll & Compensation Queue',
            'is_active' => true,
        ]);

        $erQueue = HrServiceQueue::create([
            'tenant_id' => $tenant->id,
            'code' => 'ER_SPECIALIST_QUEUE',
            'name' => 'Employee Relations Specialist Queue',
            'is_active' => true,
        ]);

        $payrollCat = HrServiceCategory::create([
            'tenant_id' => $tenant->id,
            'code' => 'CAT_PAYROLL',
            'name' => 'Payroll Inquiries',
            'is_active' => true,
        ]);

        $erCat = HrServiceCategory::create([
            'tenant_id' => $tenant->id,
            'code' => 'CAT_ER',
            'name' => 'Employee Relations & Harassment',
            'is_active' => true,
        ]);

        $payrollSvc = HrServiceDefinition::create([
            'tenant_id' => $tenant->id,
            'hr_service_category_id' => $payrollCat->id,
            'service_code' => 'SVC-PAYROLL',
            'name' => 'Tax Deduction Question',
            'status' => 'active',
        ]);

        $erSvc = HrServiceDefinition::create([
            'tenant_id' => $tenant->id,
            'hr_service_category_id' => $erCat->id,
            'service_code' => 'SVC-ER-GRIEVANCE',
            'name' => 'Confidential Grievance',
            'confidentiality_level' => 'restricted',
            'status' => 'active',
        ]);

        $routingService = app(ServiceRoutingService::class);

        // Test Payroll routing
        $payrollRoute = $routingService->determineRoute($payrollSvc, $employee);
        $this->assertEquals($payrollQueue->id, $payrollRoute['target_id']);
        $this->assertEquals('Payroll & Compensation Support Team', $payrollRoute['target_name']);

        // Test Sensitive ER routing
        $erRoute = $routingService->determineRoute($erSvc, $employee);
        $this->assertEquals($erQueue->id, $erRoute['target_id']);
        $this->assertEquals('Employee Relations & Sensitive Matters', $erRoute['target_name']);
    }

    public function test_specialist_team_and_membership_management(): void
    {
        $tenant = Tenant::factory()->create();
        $user1 = User::factory()->create(['tenant_id' => $tenant->id]);
        $user2 = User::factory()->create(['tenant_id' => $tenant->id]);

        $team = HrServiceTeam::create([
            'tenant_id' => $tenant->id,
            'code' => 'TEAM_BENEFITS_SPECIALISTS',
            'name' => 'Benefits Administration Specialists',
            'description' => 'Dedicated Tier 2 benefits experts',
            'lead_user_id' => $user1->id,
            'supported_categories' => ['Benefits'],
            'working_hours_calendar' => 'STANDARD',
            'is_active' => true,
        ]);

        $member1 = HrServiceTeamMember::create([
            'tenant_id' => $tenant->id,
            'hr_service_team_id' => $team->id,
            'user_id' => $user1->id,
            'role' => 'lead',
            'max_concurrent_capacity' => 20,
            'is_available' => true,
        ]);

        $member2 = HrServiceTeamMember::create([
            'tenant_id' => $tenant->id,
            'hr_service_team_id' => $team->id,
            'user_id' => $user2->id,
            'role' => 'specialist',
            'max_concurrent_capacity' => 15,
            'is_available' => true,
        ]);

        $this->assertCount(2, $team->members);
        $this->assertEquals('TEAM_BENEFITS_SPECIALISTS', $team->code);
        $this->assertEquals('lead', $member1->role);
    }
}
