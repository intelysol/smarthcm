<?php

namespace Tests\Feature\Absence;

use App\Domains\Absence\Models\HcmAbsencePeriod;
use App\Domains\Absence\Models\HcmAbsenceReturnToWorkPlan;
use App\Domains\Absence\Services\ReturnToWorkService;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\AttendancePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReturnToWorkAndPhasedCapacityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AttendancePermissionSeeder::class);
    }

    public function test_return_to_work_plan_phased_capacity_and_completion(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::query()->create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-RTW-01',
            'employee_number' => '200401',
            'first_name' => 'Nadia',
            'last_name' => 'Ali',
            'official_email' => 'nadia.a@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ]);

        $period = HcmAbsencePeriod::query()->create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'start_date' => '2026-10-01',
            'end_date' => '2026-11-01',
            'working_days_count' => 22,
            'calendar_days_count' => 31,
            'total_absence_hours' => 176.00,
            'absence_category' => 'unplanned_sick',
            'is_long_term' => true,
            'status' => 'active',
            'expected_return_date' => '2026-11-02',
        ]);

        $service = app(ReturnToWorkService::class);

        // 1. Create Return to Work Plan: Phased Return @ 50% capacity with operational restrictions
        $plan = $service->createReturnPlan(
            $tenant->id,
            $employee->id,
            Carbon::parse('2026-11-02'),
            $period->id,
            'phased_return',
            50.00,
            ['no_night_shifts', 'max_4_hours_standing'],
            $user->id,
            $user->id,
            'Phased return plan approved by occupational health clearance'
        );

        $this->assertEquals('active', $plan->status);
        $this->assertEquals('phased_return', $plan->return_phase);
        $this->assertEquals(50.00, $plan->capacity_percentage);
        $this->assertContains('no_night_shifts', $plan->operational_restrictions);

        // 2. Progress plan to Full Duty @ 100% capacity
        $progressed = $service->progressReturnPlan(
            $plan->id,
            'full_duty',
            100.00,
            Carbon::parse('2026-11-16')
        );

        $this->assertEquals('completed', $progressed->status);
        $this->assertEquals('full_duty', $progressed->return_phase);
        $this->assertEquals(100.00, $progressed->capacity_percentage);
        $this->assertEquals('2026-11-16', $progressed->actual_return_date->toDateString());

        $period->refresh();
        $this->assertEquals('returned', $period->status);
        $this->assertEquals('2026-11-16', $period->actual_return_date->toDateString());
    }
}