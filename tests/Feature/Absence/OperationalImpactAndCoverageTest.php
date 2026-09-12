<?php

namespace Tests\Feature\Absence;

use App\Domains\Absence\Models\HcmAbsenceOperationalImpact;
use App\Domains\Absence\Services\AbsenceOperationalImpactService;
use App\Domains\Absence\Services\AbsenceOrchestrationService;
use App\Domains\Attendance\Models\RosterAssignment;
use App\Domains\Attendance\Models\RosterPeriod;
use App\Domains\Attendance\Models\ShiftDefinition;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\AttendancePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperationalImpactAndCoverageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AttendancePermissionSeeder::class);
    }

    public function test_shift_impact_detection_and_replacement_assignment(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $absentEmp = Employee::query()->create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-ABS-03',
            'employee_number' => '200303',
            'first_name' => 'Bilal',
            'last_name' => 'Saeed',
            'official_email' => 'bilal.s@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ]);

        $replacementEmp = Employee::query()->create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-REPL-03',
            'employee_number' => '200304',
            'first_name' => 'Usman',
            'last_name' => 'Tariq',
            'official_email' => 'usman.t@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ]);

        $shift = ShiftDefinition::query()->create([
            'tenant_id' => $tenant->id,
            'shift_code' => 'DAY-8H',
            'name' => 'Day Shift',
            'start_time' => '09:00',
            'end_time' => '17:00',
            'duration_minutes' => 480,
            'is_active' => true,
        ]);

        $period = RosterPeriod::query()->create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'name' => 'November Week 1',
            'start_date' => '2026-11-01',
            'end_date' => '2026-11-07',
            'status' => 'published',
            'created_by' => $user->id,
        ]);

        $assignment = RosterAssignment::query()->create([
            'tenant_id' => $tenant->id,
            'roster_period_id' => $period->id,
            'employee_id' => $absentEmp->id,
            'roster_date' => '2026-11-03',
            'shift_definition_id' => $shift->id,
            'assignment_status' => 'scheduled',
            'created_by' => $user->id,
        ]);

        // 1. Report absence
        $orchService = app(AbsenceOrchestrationService::class);
        $event = $orchService->reportAbsence(
            $tenant->id,
            $absentEmp->id,
            Carbon::parse('2026-11-03'),
            8.00,
            'unplanned_sick'
        );

        $impact = HcmAbsenceOperationalImpact::where('absence_event_id', $event->id)->first();
        $this->assertNotNull($impact);
        $this->assertEquals('uncovered', $impact->coverage_status);
        $this->assertEquals(8.00, $impact->lost_capacity_hours);
        $this->assertEquals($shift->id, $impact->affected_shift_id);
        $this->assertEquals($assignment->id, $impact->affected_roster_assignment_id);

        // 2. Assign replacement employee via API
        $response = $this->actingAs($user)->postJson("/api/v1/hcm/absence/impacts/{$impact->id}/replace", [
            'replacement_employee_id' => $replacementEmp->id,
            'replacement_strategy' => 'open_shift',
        ]);

        $response->assertStatus(200);
        $impact->refresh();
        $this->assertEquals('covered', $impact->coverage_status);
        $this->assertEquals($replacementEmp->id, $impact->replacement_employee_id);
        $this->assertEquals('open_shift', $impact->replacement_strategy);
    }
}