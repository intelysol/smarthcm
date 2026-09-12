<?php

namespace Tests\Feature\Absence;

use App\Domains\Absence\Models\HcmAbsenceCase;
use App\Domains\Absence\Models\HcmAbsenceEvent;
use App\Domains\Absence\Models\HcmAbsencePeriod;
use App\Domains\Absence\Services\AbsenceOrchestrationService;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use Carbon\Carbon;
use Database\Seeders\AttendancePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AbsenceCaseManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AttendancePermissionSeeder::class);
    }

    public function test_long_term_absence_triggers_absence_case(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $employee = Employee::query()->create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-CASE-01',
            'employee_number' => '200501',
            'first_name' => 'Khurram',
            'last_name' => 'Shahzad',
            'official_email' => 'khurram.s@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ]);

        $service = app(AbsenceOrchestrationService::class);

        // Initial continuous period of 34 days
        HcmAbsencePeriod::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'start_date' => '2026-10-01',
            'end_date' => '2026-11-04',
            'working_days_count' => 25,
            'calendar_days_count' => 35,
            'total_absence_hours' => 200.00,
            'absence_category' => 'unplanned_sick',
            'status' => 'active',
            'is_long_term' => false,
        ]);

        // Day 35 (> 30 threshold)
        $eventDay35 = HcmAbsenceEvent::create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'absence_date' => '2026-11-05',
            'duration_hours' => 8.00,
            'absence_category' => 'unplanned_sick',
            'source' => 'manager_report',
            'status' => 'reported',
            'reported_at' => now(),
        ]);

        $period = $service->aggregateIntoPeriod($eventDay35, 30);

        $this->assertTrue($period->is_long_term);
        $this->assertDatabaseHas('hcm_absence_cases', [
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'case_type' => 'long_term_absence',
            'status' => 'opened',
        ]);
    }
}