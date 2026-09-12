<?php

namespace Tests\Feature\AttendanceTime;

use App\Domains\Attendance\Models\AttendanceAdjustment;
use App\Domains\Attendance\Models\AttendanceSession;
use App\Domains\Attendance\Models\HcmAttendanceCorrectionAudit;
use App\Domains\Attendance\Services\AttendanceCorrectionService;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\AttendancePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceCorrectionAndAuditTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AttendancePermissionSeeder::class);
    }

    public function test_attendance_correction_lifecycle_and_audit(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $employee = Employee::query()->create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-CORR',
            'employee_number' => '100303',
            'first_name' => 'Maria',
            'last_name' => 'Garcia',
            'official_email' => 'maria.g@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ]);

        $session = AttendanceSession::query()->create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'session_date' => '2026-10-02',
            'actual_start_time' => '2026-10-02 09:15:00',
            'actual_end_time' => '2026-10-02 17:00:00',
            'gross_duration_minutes' => 465,
            'net_worked_minutes' => 465,
            'regular_minutes' => 465,
            'status' => 'completed',
        ]);

        $service = app(AttendanceCorrectionService::class);

        // 1. Request correction
        $requestedValues = [
            'actual_start_time' => '2026-10-02 09:00:00',
            'actual_end_time' => '2026-10-02 17:30:00',
        ];

        $adj = $service->requestCorrection(
            $tenant->id,
            $employee->id,
            $session->id,
            'time_regularization',
            $requestedValues,
            'Biometric scanner was offline in the morning',
            $user->id
        );

        $this->assertEquals('pending', $adj->status);
        $this->assertDatabaseHas('hcm_attendance_correction_audits', [
            'attendance_adjustment_id' => $adj->id,
            'action' => 'requested',
        ]);

        // 2. Approve correction
        $approvedAdj = $service->approveCorrection($adj->id, $user->id, 'Approved after CCTV verification');

        $this->assertEquals('approved', $approvedAdj->status);
        $session->refresh();

        $this->assertTrue($session->is_adjusted);
        $this->assertEquals('2026-10-02 09:00:00', $session->actual_start_time->format('Y-m-d H:i:s'));
        $this->assertEquals('2026-10-02 17:30:00', $session->actual_end_time->format('Y-m-d H:i:s'));
        $this->assertEquals(510, $session->gross_duration_minutes); // 8.5h = 510m
        $this->assertEquals(30, $session->overtime_minutes); // 510 - 480 = 30m

        $this->assertDatabaseHas('hcm_attendance_correction_audits', [
            'attendance_adjustment_id' => $adj->id,
            'action' => 'approved',
        ]);
    }
}