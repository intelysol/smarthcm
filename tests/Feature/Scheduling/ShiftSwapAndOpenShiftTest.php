<?php

namespace Tests\Feature\Scheduling;

use App\Domains\Attendance\Models\RosterAssignment;
use App\Domains\Attendance\Models\RosterPeriod;
use App\Domains\Attendance\Models\ShiftDefinition;
use App\Domains\Attendance\Services\Scheduling\OpenShiftService;
use App\Domains\Attendance\Services\Scheduling\ShiftSwapService;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Database\Seeders\AttendancePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShiftSwapAndOpenShiftTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AttendancePermissionSeeder::class);
    }

    public function test_shift_swap_workflow_peer_accept_and_manager_approval(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $manager = User::factory()->create(['tenant_id' => $tenant->id]);

        $empA = $this->createEmployee($tenant->id, $company->id, ['first_name' => 'Alice']);
        $empB = $this->createEmployee($tenant->id, $company->id, ['first_name' => 'Bob']);

        $shiftMorning = ShiftDefinition::query()->create([
            'tenant_id' => $tenant->id,
            'shift_code' => 'MORN',
            'name' => 'Morning Shift',
            'start_time' => '08:00',
            'end_time' => '16:00',
            'duration_minutes' => 480,
            'is_active' => true,
        ]);

        $shiftEvening = ShiftDefinition::query()->create([
            'tenant_id' => $tenant->id,
            'shift_code' => 'EVE',
            'name' => 'Evening Shift',
            'start_time' => '16:00',
            'end_time' => '00:00',
            'duration_minutes' => 480,
            'is_active' => true,
        ]);

        $period = RosterPeriod::query()->create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'name' => 'Swap Period',
            'start_date' => '2026-10-19',
            'end_date' => '2026-10-25',
            'status' => 'published',
            'created_by' => $manager->id,
        ]);

        $assignA = RosterAssignment::query()->create([
            'tenant_id' => $tenant->id,
            'roster_period_id' => $period->id,
            'employee_id' => $empA->id,
            'roster_date' => '2026-10-19',
            'shift_definition_id' => $shiftMorning->id,
            'assignment_status' => 'scheduled',
            'is_published' => true,
        ]);

        $assignB = RosterAssignment::query()->create([
            'tenant_id' => $tenant->id,
            'roster_period_id' => $period->id,
            'employee_id' => $empB->id,
            'roster_date' => '2026-10-19',
            'shift_definition_id' => $shiftEvening->id,
            'assignment_status' => 'scheduled',
            'is_published' => true,
        ]);

        $swapService = app(ShiftSwapService::class);

        // 1. Employee A requests swap with Employee B
        $swapRequest = $swapService->requestSwap($assignA, $assignB, $empA, $empB, 'Family commitment');
        $this->assertEquals('pending_peer', $swapRequest->status);

        // 2. Peer Employee B accepts
        $accepted = $swapService->peerRespond($swapRequest, true);
        $this->assertEquals('pending_manager', $accepted->status);

        // 3. Manager approves swap
        $approved = $swapService->managerApprove($accepted, $manager, true);
        $this->assertEquals('approved', $approved->status);

        // Assignments must now reflect swapped shifts
        $this->assertEquals($shiftEvening->id, $assignA->fresh()->shift_definition_id);
        $this->assertEquals('swapped', $assignA->fresh()->assignment_status);
        $this->assertEquals($shiftMorning->id, $assignB->fresh()->shift_definition_id);
        $this->assertEquals('swapped', $assignB->fresh()->assignment_status);
    }

    public function test_open_shift_bidding_and_award_lifecycle(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $manager = User::factory()->create(['tenant_id' => $tenant->id]);
        $employee = $this->createEmployee($tenant->id, $company->id);

        $shift = ShiftDefinition::query()->create([
            'tenant_id' => $tenant->id,
            'shift_code' => 'WEEKEND',
            'name' => 'Weekend Shift',
            'start_time' => '10:00',
            'end_time' => '18:00',
            'duration_minutes' => 480,
            'is_active' => true,
        ]);

        $period = RosterPeriod::query()->create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'name' => 'Open Shift Week',
            'start_date' => '2026-10-24',
            'end_date' => '2026-10-25',
            'status' => 'published',
            'created_by' => $manager->id,
        ]);

        $openShiftService = app(OpenShiftService::class);

        // 1. Manager publishes Open Shift
        $openShift = $openShiftService->createOpenShift(
            period: $period,
            date: '2026-10-24',
            shift: $shift,
            slots: 1
        );
        $this->assertEquals('open', $openShift->status);
        $this->assertEquals(0, $openShift->slots_filled);

        // 2. Eligible Employee bids for shift
        $bid = $openShiftService->submitBid($openShift, $employee);
        $this->assertEquals('submitted', $bid->bid_status);
        $this->assertGreaterThan(0, $bid->eligibility_score);

        // 3. Manager awards open shift to employee
        $assignment = $openShiftService->awardBid($bid, $manager);

        $this->assertEquals('scheduled', $assignment->assignment_status);
        $this->assertEquals('selected', $bid->fresh()->bid_status);
        $this->assertEquals('filled', $openShift->fresh()->status);
        $this->assertEquals(1, $openShift->fresh()->slots_filled);
    }

    protected function createEmployee(string $tenantId, string $companyId, array $attributes = []): Employee
    {
        return Employee::query()->create(array_merge([
            'tenant_id' => $tenantId,
            'company_id' => $companyId,
            'employee_code' => 'EMP-' . uniqid(),
            'employee_number' => 'EMP-' . rand(1000, 9999),
            'first_name' => 'Employee',
            'last_name' => 'Tester',
            'official_email' => 'emp.' . uniqid() . '@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ], $attributes));
    }
}
