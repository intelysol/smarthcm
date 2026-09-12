<?php

namespace Tests\Feature\Scheduling;

use App\Domains\Attendance\Models\AttendanceSession;
use App\Domains\Attendance\Models\RosterAssignment;
use App\Domains\Attendance\Models\RosterPeriod;
use App\Domains\Attendance\Models\ShiftDefinition;
use App\Domains\Attendance\Services\Scheduling\RealTimeCoverageService;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use Database\Seeders\AttendancePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RealTimeCoverageAndExceptionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AttendancePermissionSeeder::class);
    }

    public function test_real_time_coverage_and_no_show_detection(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $empPresent = $this->createEmployee($tenant->id, $company->id, ['first_name' => 'PresentWorker']);
        $empLate = $this->createEmployee($tenant->id, $company->id, ['first_name' => 'LateWorker']);
        $empNoShow = $this->createEmployee($tenant->id, $company->id, ['first_name' => 'NoShowWorker']);

        $shift = ShiftDefinition::query()->create([
            'tenant_id' => $tenant->id,
            'shift_code' => 'MORN',
            'name' => 'Morning Shift',
            'start_time' => '08:00',
            'end_time' => '16:00',
            'duration_minutes' => 480,
            'is_active' => true,
        ]);

        $period = RosterPeriod::query()->create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'name' => 'Real-Time Period',
            'start_date' => '2026-10-15',
            'end_date' => '2026-10-20',
            'status' => 'published',
        ]);

        // 3 Scheduled employees on 2026-10-15
        foreach ([$empPresent, $empLate, $empNoShow] as $emp) {
            RosterAssignment::query()->create([
                'tenant_id' => $tenant->id,
                'roster_period_id' => $period->id,
                'employee_id' => $emp->id,
                'roster_date' => '2026-10-15',
                'shift_definition_id' => $shift->id,
                'assignment_status' => 'scheduled',
                'is_published' => true,
            ]);
        }

        // Attendance session 1: On time
        AttendanceSession::query()->create([
            'tenant_id' => $tenant->id,
            'employee_id' => $empPresent->id,
            'session_date' => '2026-10-15',
            'actual_start_time' => '2026-10-15 08:00:00',
            'status' => 'present',
            'late_minutes' => 0,
        ]);

        // Attendance session 2: Late
        AttendanceSession::query()->create([
            'tenant_id' => $tenant->id,
            'employee_id' => $empLate->id,
            'session_date' => '2026-10-15',
            'actual_start_time' => '2026-10-15 08:45:00',
            'status' => 'present',
            'late_minutes' => 45,
        ]);

        // Attendance session 3: None (No-Show)

        $realTimeService = app(RealTimeCoverageService::class);
        $result = $realTimeService->evaluateRealTimeCoverage($tenant->id, '2026-10-15');

        $this->assertEquals(3, $result['total_scheduled']);
        $this->assertEquals(2, $result['total_present']);
        $this->assertEquals(1, $result['total_no_shows']);
        $this->assertEquals(1, $result['total_late']);
        $this->assertEquals(66.7, $result['real_time_coverage_pct']);
        $this->assertEquals(2, $result['exceptions_generated']); // 1 late + 1 no-show

        $this->assertDatabaseHas('hcm_schedule_exceptions', [
            'tenant_id' => $tenant->id,
            'employee_id' => $empNoShow->id,
            'exception_type' => 'no_show',
            'severity' => 'critical',
        ]);

        $this->assertDatabaseHas('hcm_schedule_exceptions', [
            'tenant_id' => $tenant->id,
            'employee_id' => $empLate->id,
            'exception_type' => 'late_arrival',
            'severity' => 'warning',
        ]);
    }

    protected function createEmployee(string $tenantId, string $companyId, array $attributes = []): Employee
    {
        return Employee::query()->create(array_merge([
            'tenant_id' => $tenantId,
            'company_id' => $companyId,
            'employee_code' => 'EMP-' . uniqid(),
            'employee_number' => 'EMP-' . rand(1000, 9999),
            'first_name' => 'Worker',
            'last_name' => 'Shift',
            'official_email' => 'worker.' . uniqid() . '@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ], $attributes));
    }
}
