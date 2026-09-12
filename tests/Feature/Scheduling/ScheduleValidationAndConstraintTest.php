<?php

namespace Tests\Feature\Scheduling;

use App\Domains\Attendance\Models\HcmEmployeeAvailability;
use App\Domains\Attendance\Models\RosterAssignment;
use App\Domains\Attendance\Models\RosterPeriod;
use App\Domains\Attendance\Models\ShiftDefinition;
use App\Domains\Attendance\Services\Scheduling\ScheduleEligibilityService;
use App\Domains\Attendance\Services\Scheduling\ScheduleValidationService;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Database\Seeders\AttendancePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ScheduleValidationAndConstraintTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AttendancePermissionSeeder::class);
    }

    public function test_leave_conflict_and_inactive_status_are_hard_violations(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $empActive = $this->createEmployee($tenant->id, $company->id, ['employment_status' => 'active']);
        $empInactive = $this->createEmployee($tenant->id, $company->id, ['employment_status' => 'terminated']);

        $shift = ShiftDefinition::query()->create([
            'tenant_id' => $tenant->id,
            'shift_code' => 'MORN',
            'name' => 'Morning Shift',
            'start_time' => '08:00',
            'end_time' => '16:00',
            'duration_minutes' => 480,
            'is_active' => true,
        ]);

        $eligibilityService = app(ScheduleEligibilityService::class);

        // 1. Inactive employee should have hard violation
        $resInactive = $eligibilityService->checkEligibility($empInactive, '2026-10-05', $shift);
        $this->assertFalse($resInactive['is_eligible']);
        $this->assertStringContainsString('inactive', $resInactive['hard_violations'][0]);

        // 2. Active employee on approved leave should have hard violation
        $leaveTypeId = (string) \Illuminate\Support\Str::uuid();
        DB::table('leave_types')->insert([
            'id' => $leaveTypeId,
            'tenant_id' => $tenant->id,
            'name' => 'Annual Leave',
            'code' => 'AL-' . uniqid(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('leave_applications')->insert([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'tenant_id' => $tenant->id,
            'employee_id' => $empActive->id,
            'leave_type_id' => $leaveTypeId,
            'start_date' => '2026-10-05',
            'end_date' => '2026-10-07',
            'duration' => 3.00,
            'status' => 'approved',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $resLeave = $eligibilityService->checkEligibility($empActive, '2026-10-05', $shift);
        $this->assertFalse($resLeave['is_eligible']);
        $this->assertStringContainsString('approved leave', $resLeave['hard_violations'][0]);
    }

    public function test_schedule_validation_and_quality_score_calculation(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $employee = $this->createEmployee($tenant->id, $company->id);

        $shift = ShiftDefinition::query()->create([
            'tenant_id' => $tenant->id,
            'shift_code' => 'MORN-1',
            'name' => 'Morning Shift 1',
            'start_time' => '08:00',
            'end_time' => '16:00',
            'duration_minutes' => 480,
            'is_active' => true,
        ]);

        $period = RosterPeriod::query()->create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'name' => 'October Week 1',
            'start_date' => '2026-10-05',
            'end_date' => '2026-10-11',
            'status' => 'draft',
            'created_by' => $user->id,
        ]);

        RosterAssignment::query()->create([
            'tenant_id' => $tenant->id,
            'roster_period_id' => $period->id,
            'employee_id' => $employee->id,
            'roster_date' => '2026-10-05',
            'shift_definition_id' => $shift->id,
            'assignment_status' => 'scheduled',
            'created_by' => $user->id,
        ]);

        $validationService = app(ScheduleValidationService::class);
        $summary = $validationService->validatePeriod($period);

        $this->assertEquals('valid', $summary['validation_status']);
        $this->assertEquals(0, $summary['critical_count']);
        $this->assertGreaterThan(0, $summary['schedule_quality_score']);
        $this->assertDatabaseHas('roster_periods', [
            'id' => $period->id,
            'validation_status' => 'valid',
        ]);
    }

    protected function createEmployee(string $tenantId, string $companyId, array $attributes = []): Employee
    {
        return Employee::query()->create(array_merge([
            'tenant_id' => $tenantId,
            'company_id' => $companyId,
            'employee_code' => 'EMP-' . uniqid(),
            'employee_number' => 'EMP-' . rand(1000, 9999),
            'first_name' => 'Sara',
            'last_name' => 'Ahmed',
            'official_email' => 'sara.' . uniqid() . '@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ], $attributes));
    }
}
