<?php

namespace Tests\Feature\Scheduling;

use App\Domains\Attendance\Models\HcmEmployeeShiftPreference;
use App\Domains\Attendance\Models\HcmScheduleCoverageRequirement;
use App\Domains\Attendance\Models\RosterPeriod;
use App\Domains\Attendance\Models\ShiftDefinition;
use App\Domains\Attendance\Services\Scheduling\ScheduleOptimizationService;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Database\Seeders\AttendancePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScheduleOptimizationEngineTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AttendancePermissionSeeder::class);
    }

    public function test_optimization_generates_proposals_without_auto_publishing(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $manager = User::factory()->create(['tenant_id' => $tenant->id]);

        $emp1 = $this->createEmployee($tenant->id, $company->id, ['first_name' => 'EmpOne']);
        $emp2 = $this->createEmployee($tenant->id, $company->id, ['first_name' => 'EmpTwo']);

        $shift = ShiftDefinition::query()->create([
            'tenant_id' => $tenant->id,
            'shift_code' => 'MORN',
            'name' => 'Morning Ops',
            'start_time' => '08:00',
            'end_time' => '16:00',
            'duration_minutes' => 480,
            'is_active' => true,
        ]);

        $period = RosterPeriod::query()->create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'name' => 'October Week 2',
            'start_date' => '2026-10-12',
            'end_date' => '2026-10-14',
            'status' => 'draft',
            'created_by' => $manager->id,
        ]);

        // Coverage requirement: Need 2 staff on 2026-10-12
        HcmScheduleCoverageRequirement::query()->create([
            'tenant_id' => $tenant->id,
            'roster_period_id' => $period->id,
            'requirement_date' => '2026-10-12',
            'shift_definition_id' => $shift->id,
            'required_headcount' => 2,
        ]);

        // Employee 1 prefers this shift (+priority)
        HcmEmployeeShiftPreference::query()->create([
            'tenant_id' => $tenant->id,
            'employee_id' => $emp1->id,
            'shift_definition_id' => $shift->id,
            'preference_type' => 'preferred',
            'priority' => 3,
        ]);

        $optimizationService = app(ScheduleOptimizationService::class);

        // 1. Run Optimization
        $run = $optimizationService->optimize($period, 'rule_based_heuristic', $manager);

        $this->assertEquals('completed', $run->status);
        $this->assertNotEmpty($run->proposed_assignments);
        $this->assertCount(2, $run->proposed_assignments);

        // Crucial architecture rule: zero live assignments before manager approval
        $this->assertEquals(0, $period->assignments()->count());

        // 2. Manager Reviews and Applies Proposals
        $appliedCount = $optimizationService->applyProposedAssignments($run, $manager);

        $this->assertEquals(2, $appliedCount);
        $this->assertEquals(2, $period->assignments()->count());
        $this->assertDatabaseHas('roster_assignments', [
            'tenant_id' => $tenant->id,
            'roster_period_id' => $period->id,
            'employee_id' => $emp1->id,
            'shift_definition_id' => $shift->id,
            'assignment_status' => 'scheduled',
            'is_published' => false,
        ]);
    }

    protected function createEmployee(string $tenantId, string $companyId, array $attributes = []): Employee
    {
        return Employee::query()->create(array_merge([
            'tenant_id' => $tenantId,
            'company_id' => $companyId,
            'employee_code' => 'EMP-' . uniqid(),
            'employee_number' => 'EMP-' . rand(1000, 9999),
            'first_name' => 'John',
            'last_name' => 'Doe',
            'official_email' => 'john.' . uniqid() . '@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ], $attributes));
    }
}
