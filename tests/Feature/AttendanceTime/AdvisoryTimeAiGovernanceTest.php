<?php

namespace Tests\Feature\AttendanceTime;

use App\Domains\Attendance\Models\Timesheet;
use App\Domains\Attendance\Services\AdvisoryTimeAiService;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use Carbon\Carbon;
use Database\Seeders\AttendancePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdvisoryTimeAiGovernanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AttendancePermissionSeeder::class);
    }

    public function test_advisory_ai_output_and_governance_rules(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $employee = Employee::query()->create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-AI',
            'employee_number' => '100808',
            'first_name' => 'Alan',
            'last_name' => 'Turing',
            'official_email' => 'alan.t@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ]);

        $timesheet = Timesheet::query()->create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-07',
            'total_scheduled_minutes' => 2400, // 40 hours
            'total_worked_minutes' => 2700,    // 45 hours
            'total_regular_minutes' => 2400,
            'total_overtime_minutes' => 300,
            'status' => 'draft',
        ]);

        $service = app(AdvisoryTimeAiService::class);

        // 1. Anomaly analysis
        $analysis = $service->analyzeAttendanceAnomalies(
            $employee->id,
            Carbon::parse('2026-10-01'),
            Carbon::parse('2026-10-07')
        );

        $this->assertTrue($analysis['is_advisory_only']);
        $this->assertFalse($analysis['autonomous_actions_permitted']);
        $this->assertArrayHasKey('insights', $analysis);

        // 2. Timesheet variance explanation
        $variance = $service->explainTimesheetVariance($timesheet->id);

        $this->assertTrue($variance['is_advisory_only']);
        $this->assertFalse($variance['autonomous_actions_permitted']);
        $this->assertEquals(40.00, $variance['summary']['scheduled_hours']);
        $this->assertEquals(45.00, $variance['summary']['worked_hours']);
        $this->assertEquals(5.00, $variance['summary']['variance_hours']);
    }
}