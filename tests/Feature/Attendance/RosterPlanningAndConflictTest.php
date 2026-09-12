<?php

namespace Tests\Feature\Attendance;

use App\Domains\Attendance\Enums\RosterStatus;
use App\Domains\Attendance\Models\RosterAssignment;
use App\Domains\Attendance\Models\ShiftDefinition;
use App\Domains\Attendance\Services\RosterConflictEngine;
use App\Domains\Attendance\Services\RosterService;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Database\Seeders\AttendanceDefaultDataSeeder;
use Database\Seeders\AttendancePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RosterPlanningAndConflictTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AttendancePermissionSeeder::class);
    }

    public function test_roster_assignment_and_publishing(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $employee = $this->createEmployee($tenant->id, $company->id, ['user_id' => $user->id]);

        $seeder = new AttendanceDefaultDataSeeder();
        $seeder->seedTenantData($tenant->id);

        $shift = ShiftDefinition::query()->where('tenant_id', $tenant->id)->where('shift_code', 'SHIFT-MORN')->first();
        $rosterService = app(RosterService::class);

        // 1. Create Roster Period
        $period = $rosterService->createPeriod($tenant->id, [
            'name' => 'Sprint Schedule 1',
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-14',
            'company_id' => $company->id,
        ], $user);

        $this->assertEquals(RosterStatus::DRAFT->value, $period->status);

        // 2. Assign Shift
        $assignment = $rosterService->assignShift($period, $employee, '2026-09-07', $shift, $user, 'Assigned to morning ops');
        $this->assertEquals('scheduled', $assignment->assignment_status);
        $this->assertFalse($assignment->is_published);

        // 3. Publish Roster
        $published = $rosterService->publishRoster($period, $user);
        $this->assertEquals(RosterStatus::PUBLISHED->value, $published->status);
        $this->assertTrue($assignment->fresh()->is_published);
    }

    public function test_roster_conflict_detection_on_insufficient_rest_period(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $employee = $this->createEmployee($tenant->id, $company->id);

        $seeder = new AttendanceDefaultDataSeeder();
        $seeder->seedTenantData($tenant->id);

        $eveningShift = ShiftDefinition::query()->where('tenant_id', $tenant->id)->where('shift_code', 'SHIFT-EVE')->first();
        $morningShift = ShiftDefinition::query()->where('tenant_id', $tenant->id)->where('shift_code', 'SHIFT-MORN')->first();

        $rosterService = app(RosterService::class);
        $conflictEngine = app(RosterConflictEngine::class);

        $period = $rosterService->createPeriod($tenant->id, [
            'name' => 'Conflict Test Period',
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-14',
        ], $user);

        // Day 1: Evening shift ending at 00:00 midnight
        $rosterService->assignShift($period, $employee, '2026-09-07', $eveningShift, $user);

        // Day 2: Morning shift starting at 08:00 AM (only 8 hours rest, less than 11 hours minimum)
        $conflicts = $conflictEngine->detectConflicts($employee, '2026-09-08', $morningShift);

        $this->assertNotEmpty($conflicts);
        $restConflict = collect($conflicts)->firstWhere('type', 'insufficient_rest_period');
        $this->assertNotNull($restConflict);
        $this->assertEquals('warning', $restConflict['severity']);
        $this->assertStringContainsString('8h rest', $restConflict['message']);
    }

    public function test_shift_swap_between_employees(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $emp1 = $this->createEmployee($tenant->id, $company->id);
        $emp2 = $this->createEmployee($tenant->id, $company->id);

        $seeder = new AttendanceDefaultDataSeeder();
        $seeder->seedTenantData($tenant->id);

        $shiftA = ShiftDefinition::query()->where('tenant_id', $tenant->id)->where('shift_code', 'SHIFT-MORN')->first();
        $shiftB = ShiftDefinition::query()->where('tenant_id', $tenant->id)->where('shift_code', 'SHIFT-EVE')->first();

        $rosterService = app(RosterService::class);
        $period = $rosterService->createPeriod($tenant->id, ['name' => 'Swap Period', 'start_date' => '2026-09-01', 'end_date' => '2026-09-14'], $user);

        $assign1 = $rosterService->assignShift($period, $emp1, '2026-09-07', $shiftA, $user);
        $assign2 = $rosterService->assignShift($period, $emp2, '2026-09-07', $shiftB, $user);

        $rosterService->swapShifts($assign1, $assign2, $user);

        $this->assertEquals($shiftB->id, $assign1->fresh()->shift_definition_id);
        $this->assertEquals('swapped', $assign1->fresh()->assignment_status);
        $this->assertEquals($shiftA->id, $assign2->fresh()->shift_definition_id);
        $this->assertEquals('swapped', $assign2->fresh()->assignment_status);
    }

    protected function createEmployee(string $tenantId, string $companyId, array $attributes = []): Employee
    {
        return Employee::query()->create(array_merge([
            'tenant_id' => $tenantId,
            'company_id' => $companyId,
            'employee_code' => 'EMP-' . uniqid(),
            'employee_number' => 'EMP-' . rand(1000, 9999),
            'first_name' => 'John',
            'last_name' => 'Doe',
            'official_email' => 'john.' . uniqid() . '@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ], $attributes));
    }
}
