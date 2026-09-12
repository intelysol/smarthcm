<?php

namespace Tests\Feature\Absence;

use App\Domains\Absence\Models\HcmAbsenceEvent;
use App\Domains\Absence\Services\AbsenceReconciliationService;
use App\Domains\Attendance\Models\AttendanceSession;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\AttendancePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MultiWayAbsenceReconciliationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AttendancePermissionSeeder::class);
    }

    public function test_multi_way_absence_reconciliation_detects_discrepancies(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $emp1 = Employee::query()->create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-REC-01',
            'employee_number' => '200601',
            'first_name' => 'Ayesha',
            'last_name' => 'Malik',
            'official_email' => 'ayesha.m@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ]);

        // Discrepancy 1: Clock-in punch during approved leave
        $leaveTypeId = (string) \Illuminate\Support\Str::uuid();
        DB::table('leave_types')->insert([
            'id' => $leaveTypeId,
            'tenant_id' => $tenant->id,
            'name' => 'Sick Leave',
            'code' => 'SL-' . uniqid(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('leave_applications')->insert([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'tenant_id' => $tenant->id,
            'employee_id' => $emp1->id,
            'leave_type_id' => $leaveTypeId,
            'start_date' => '2026-11-04',
            'end_date' => '2026-11-04',
            'duration' => 1.00,
            'status' => 'approved',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        AttendanceSession::query()->create([
            'tenant_id' => $tenant->id,
            'employee_id' => $emp1->id,
            'session_date' => '2026-11-04',
            'actual_start_time' => '2026-11-04 09:00:00',
            'actual_end_time' => '2026-11-04 17:00:00',
            'gross_duration_minutes' => 480,
            'net_worked_minutes' => 480,
            'status' => 'completed',
        ]);

        // Discrepancy 2: Unplanned absence without leave application
        HcmAbsenceEvent::create([
            'tenant_id' => $tenant->id,
            'employee_id' => $emp1->id,
            'absence_date' => '2026-11-06',
            'duration_hours' => 8.00,
            'absence_category' => 'unplanned_sick',
            'source' => 'employee_self_service',
            'is_planned' => false,
            'status' => 'reported',
            'reported_at' => now(),
        ]);

        $service = app(AbsenceReconciliationService::class);
        $rec = $service->reconcilePeriod(
            $tenant->id,
            Carbon::parse('2026-11-01'),
            Carbon::parse('2026-11-07'),
            $user->id
        );

        $this->assertEquals('discrepancy_detected', $rec->status);
        $this->assertEquals(2, $rec->discrepant_records_count);
        $this->assertCount(2, $rec->discrepancies);
    }
}