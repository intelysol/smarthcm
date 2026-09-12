<?php

namespace Tests\Feature\Absence;

use App\Domains\Absence\Models\HcmAbsenceEvent;
use App\Domains\Absence\Models\HcmAbsencePeriod;
use App\Domains\Absence\Services\AbsenceOrchestrationService;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\AttendancePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AbsenceOrchestrationAndEventTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AttendancePermissionSeeder::class);
    }

    public function test_planned_and_unplanned_absence_reporting_and_period_aggregation(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $employee = Employee::query()->create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-ABS-01',
            'employee_number' => '200101',
            'first_name' => 'Farah',
            'last_name' => 'Khan',
            'official_email' => 'farah.k@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ]);

        $service = app(AbsenceOrchestrationService::class);

        // 1. Ingest approved leave (planned)
        $leaveTypeId = (string) \Illuminate\Support\Str::uuid();
        DB::table('leave_types')->insert([
            'id' => $leaveTypeId,
            'tenant_id' => $tenant->id,
            'name' => 'Annual Leave',
            'code' => 'AL-' . uniqid(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $leaveAppId = (string) \Illuminate\Support\Str::uuid();
        DB::table('leave_applications')->insert([
            'id' => $leaveAppId,
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveTypeId,
            'start_date' => '2026-11-02',
            'end_date' => '2026-11-04',
            'duration' => 3.00,
            'status' => 'approved',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $plannedEvent = $service->recordLeaveAbsence(
            $tenant->id,
            $employee->id,
            $leaveAppId,
            Carbon::parse('2026-11-02'),
            Carbon::parse('2026-11-04'),
            24.00,
            'vacation'
        );

        $this->assertTrue($plannedEvent->is_planned);
        $this->assertEquals('leave', $plannedEvent->source);
        $this->assertEquals(24.00, $plannedEvent->duration_hours);

        // 2. Report unplanned sickness via self-service API
        $payload = [
            'employee_id' => $employee->id,
            'absence_date' => '2026-11-10',
            'duration_hours' => 8.00,
            'absence_category' => 'unplanned_sick',
            'source' => 'employee_self_service',
        ];

        $response = $this->actingAs($user)->postJson('/api/v1/hcm/absence/events', $payload);
        $response->assertStatus(201);

        $unplannedEvent = HcmAbsenceEvent::where('employee_id', $employee->id)
            ->whereDate('absence_date', '2026-11-10')
            ->first();

        $this->assertNotNull($unplannedEvent);
        $this->assertFalse($unplannedEvent->is_planned);
        $this->assertEquals('unplanned_sick', $unplannedEvent->absence_category);

        // 3. Verify continuous period aggregation
        $service->reportAbsence($tenant->id, $employee->id, Carbon::parse('2026-11-11'), 8.00, 'unplanned_sick');
        $service->reportAbsence($tenant->id, $employee->id, Carbon::parse('2026-11-12'), 8.00, 'unplanned_sick');

        $period = HcmAbsencePeriod::where('tenant_id', $tenant->id)
            ->where('employee_id', $employee->id)
            ->whereDate('start_date', '2026-11-10')
            ->first();

        $this->assertNotNull($period);
        $this->assertEquals('2026-11-12', $period->end_date->toDateString());
        $this->assertEquals(24.00, $period->total_absence_hours);
    }
}