<?php

namespace Tests\Feature\AttendanceTime;

use App\Domains\Attendance\Models\HcmOvertimeTierRecord;
use App\Domains\Attendance\Services\OvertimeTierCalculationService;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\AttendancePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiTierOvertimeEngineTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AttendancePermissionSeeder::class);
    }

    public function test_multi_tier_overtime_calculation_and_approval(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $employee = Employee::query()->create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-OT',
            'employee_number' => '100505',
            'first_name' => 'Elena',
            'last_name' => 'Rostova',
            'official_email' => 'elena.r@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ]);

        $service = app(OvertimeTierCalculationService::class);

        // Case 1: Regular workday with 13 hours (780m) worked. Standard = 8h (480m). OT = 5h (300m).
        // Tier 1 (1.5x) = 120m (2h)
        // Tier 2 (2.0x) = 120m (2h)
        // Tier 3 (2.5x) = 60m (1h)
        $rec1 = $service->calculateDailyOvertimeTiers(
            $tenant->id,
            $employee->id,
            Carbon::parse('2026-10-06'),
            780,
            480
        );

        $this->assertEquals(120, $rec1->tier_1_minutes);
        $this->assertEquals(120, $rec1->tier_2_minutes);
        $this->assertEquals(60, $rec1->tier_3_minutes);
        $this->assertEquals(300, $rec1->total_overtime_minutes);
        $this->assertTrue($rec1->is_unauthorized);
        $this->assertEquals('pending_approval', $rec1->status);

        // Approve overtime
        $approvedRec = $service->approveOvertime($rec1->id, $user->id);
        $this->assertEquals('approved', $approvedRec->status);
        $this->assertFalse($approvedRec->is_unauthorized);

        // Case 2: Public Holiday workday with 6 hours (360m) worked. All goes to Tier 3.
        $recHoliday = $service->calculateDailyOvertimeTiers(
            $tenant->id,
            $employee->id,
            Carbon::parse('2026-10-12'),
            360,
            480,
            false,
            true // isHoliday = true
        );

        $this->assertEquals(0, $recHoliday->tier_1_minutes);
        $this->assertEquals(0, $recHoliday->tier_2_minutes);
        $this->assertEquals(360, $recHoliday->tier_3_minutes);
        $this->assertEquals('holiday_overtime', $recHoliday->overtime_category);
    }
}