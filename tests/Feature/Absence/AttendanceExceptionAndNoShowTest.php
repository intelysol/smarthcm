<?php

namespace Tests\Feature\Absence;

use App\Domains\Absence\Services\AbsenceOrchestrationService;
use App\Domains\Attendance\Models\AttendanceException;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use Carbon\Carbon;
use Database\Seeders\AttendancePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceExceptionAndNoShowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AttendancePermissionSeeder::class);
    }

    public function test_no_show_attendance_exception_converts_to_unplanned_absence(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $employee = Employee::query()->create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-NOSHOW',
            'employee_number' => '200202',
            'first_name' => 'Rashid',
            'last_name' => 'Qureshi',
            'official_email' => 'rashid.q@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ]);

        $exception = AttendanceException::query()->create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'exception_date' => '2026-11-05',
            'exception_type' => 'no_show',
            'severity' => 'critical',
            'status' => 'detected',
            'explanation' => 'Employee scheduled for morning shift but did not clock in within grace period.',
        ]);

        $service = app(AbsenceOrchestrationService::class);
        $event = $service->recordNoShowAbsence(
            $tenant->id,
            $employee->id,
            $exception->id,
            Carbon::parse('2026-11-05'),
            8.00
        );

        $this->assertEquals('attendance_exception', $event->source);
        $this->assertFalse($event->is_planned);
        $this->assertEquals($exception->id, $event->attendance_exception_id);
        $this->assertEquals('unplanned_sick', $event->absence_category);
    }
}