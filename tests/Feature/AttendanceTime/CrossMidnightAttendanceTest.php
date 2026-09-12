<?php

namespace Tests\Feature\AttendanceTime;

use App\Domains\Attendance\Models\ShiftDefinition;
use App\Domains\Attendance\Services\CrossMidnightAttendanceService;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use Carbon\Carbon;
use Database\Seeders\AttendancePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrossMidnightAttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AttendancePermissionSeeder::class);
    }

    public function test_overnight_shift_punch_pairing_and_operational_date(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $employee = Employee::query()->create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-NIGHT',
            'employee_number' => '100202',
            'first_name' => 'Alex',
            'last_name' => 'Stone',
            'official_email' => 'alex.stone@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ]);

        $nightShift = ShiftDefinition::query()->create([
            'tenant_id' => $tenant->id,
            'shift_code' => 'NIGHT-1',
            'name' => 'Overnight Shift',
            'start_time' => '22:00',
            'end_time' => '06:00',
            'standard_hours_minutes' => 480,
            'duration_minutes' => 480,
            'is_active' => true,
        ]);

        $service = app(CrossMidnightAttendanceService::class);
        $this->assertTrue($service->isOvernightShift($nightShift));

        $inTime = Carbon::parse('2026-10-01 21:55:00');
        $outTime = Carbon::parse('2026-10-02 06:10:00');

        $session = $service->pairOvernightPunches($tenant->id, $employee->id, $inTime, $outTime, $nightShift, 60);

        $this->assertEquals('2026-10-01', $session->session_date->toDateString());
        $this->assertTrue($session->is_overnight);
        $this->assertEquals(495, $session->gross_duration_minutes); // 21:55 to 06:10 = 8h 15m = 495m
        $this->assertEquals(435, $session->net_worked_minutes); // 495 - 60 break = 435m
        $this->assertEquals(435, $session->regular_minutes);
        $this->assertEquals(0, $session->overtime_minutes);

        $this->assertDatabaseHas('attendance_sessions', [
            'id' => $session->id,
            'is_overnight' => 1,
        ]);
    }
}