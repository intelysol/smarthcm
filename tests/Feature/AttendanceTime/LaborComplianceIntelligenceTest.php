<?php

namespace Tests\Feature\AttendanceTime;

use App\Domains\Attendance\Models\AttendanceSession;
use App\Domains\Attendance\Models\HcmLaborComplianceCheck;
use App\Domains\Attendance\Services\LaborComplianceIntelligenceService;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Database\Seeders\AttendancePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaborComplianceIntelligenceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AttendancePermissionSeeder::class);
    }

    public function test_labor_compliance_detection_and_waiver(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $employee = Employee::query()->create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-COMPL',
            'employee_number' => '100606',
            'first_name' => 'Tariq',
            'last_name' => 'Mansoor',
            'official_email' => 'tariq.m@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ]);

        // Session with 14 hours (840m) worked without break: violates MAX_DAILY_HOURS (>12h) and MANDATORY_MEAL_BREAK
        $session = AttendanceSession::query()->create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'session_date' => '2026-10-07',
            'actual_start_time' => '2026-10-07 07:00:00',
            'actual_end_time' => '2026-10-07 21:00:00',
            'gross_duration_minutes' => 840,
            'net_worked_minutes' => 840,
            'total_break_minutes' => 0,
            'unpaid_break_minutes' => 0,
        ]);

        $service = app(LaborComplianceIntelligenceService::class);
        $checks = $service->evaluateSessionCompliance($session);

        $this->assertCount(2, $checks);
        $this->assertTrue($checks->contains('rule_code', 'MAX_DAILY_HOURS'));
        $this->assertTrue($checks->contains('rule_code', 'MANDATORY_MEAL_BREAK'));

        // Test waiving one check
        $checkToWaive = $checks->firstWhere('rule_code', 'MAX_DAILY_HOURS');
        $waived = $service->waiveComplianceCheck($checkToWaive->id, $user->id, 'Emergency system restoration waiver');

        $this->assertEquals('waived', $waived->status);
        $this->assertEquals('Emergency system restoration waiver', $waived->waiver_reason);
    }
}