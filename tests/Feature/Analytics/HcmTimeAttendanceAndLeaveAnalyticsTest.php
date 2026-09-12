<?php

namespace Tests\Feature\Analytics;

use App\Domains\Analytics\Services\HcmTimeAndAttendanceAnalyticsService;
use App\Domains\Attendance\Models\AttendanceSession;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\BusinessUnit;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Department;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HcmTimeAttendanceAndLeaveAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_attendance_rate_absenteeism_and_overtime_metrics(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $bu = BusinessUnit::create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'name' => 'HQ Unit', 'code' => 'BU-01']);
        $dept = Department::create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'business_unit_id' => $bu->id, 'department_name' => 'Support', 'department_code' => 'SUP']);

        $emp = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'department_id' => $dept->id,
            'employee_code' => 'EMP-ATT-01',
            'employee_number' => 'EMP-ATT-01',
            'first_name' => 'Support',
            'last_name' => 'Agent',
            'official_email' => 'support.agent@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ]);

        // Create Attendance Sessions
        AttendanceSession::create([
            'tenant_id' => $tenant->id,
            'employee_id' => $emp->id,
            'session_date' => '2026-03-01',
            'status' => 'present',
            'net_worked_minutes' => 480,
            'overtime_minutes' => 60,
            'late_minutes' => 0,
        ]);

        AttendanceSession::create([
            'tenant_id' => $tenant->id,
            'employee_id' => $emp->id,
            'session_date' => '2026-03-02',
            'status' => 'present',
            'net_worked_minutes' => 450,
            'overtime_minutes' => 0,
            'late_minutes' => 30,
        ]);

        AttendanceSession::create([
            'tenant_id' => $tenant->id,
            'employee_id' => $emp->id,
            'session_date' => '2026-03-03',
            'status' => 'absent',
            'net_worked_minutes' => 0,
            'overtime_minutes' => 0,
            'late_minutes' => 0,
        ]);

        $service = app(HcmTimeAndAttendanceAnalyticsService::class);
        $metrics = $service->getAttendanceMetrics($tenant->id, '2026-03-01', '2026-03-03');

        $this->assertEquals(3, $metrics['total_scheduled_days']);
        $this->assertEquals(2, $metrics['present_days']);
        $this->assertEquals(1, $metrics['absent_days']);
        $this->assertEquals(1, $metrics['late_days']);
        $this->assertEquals(66.67, $metrics['attendance_rate_percent']);
        $this->assertEquals(33.33, $metrics['absenteeism_rate_percent']);
        $this->assertEquals(1.0, (float) $metrics['total_overtime_hours']);
    }
}
