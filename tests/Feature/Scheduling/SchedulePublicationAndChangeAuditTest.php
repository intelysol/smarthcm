<?php

namespace Tests\Feature\Scheduling;

use App\Domains\Attendance\Models\RosterAssignment;
use App\Domains\Attendance\Models\RosterPeriod;
use App\Domains\Attendance\Models\ShiftDefinition;
use App\Domains\Attendance\Services\Scheduling\SchedulePublicationService;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Database\Seeders\AttendancePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SchedulePublicationAndChangeAuditTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AttendancePermissionSeeder::class);
    }

    public function test_publishing_blocked_on_critical_violation_and_succeeds_when_valid(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $manager = User::factory()->create(['tenant_id' => $tenant->id]);

        $empInactive = $this->createEmployee($tenant->id, $company->id, ['employment_status' => 'terminated']);
        $empActive = $this->createEmployee($tenant->id, $company->id, ['employment_status' => 'active']);

        $shift = ShiftDefinition::query()->create([
            'tenant_id' => $tenant->id,
            'shift_code' => 'MORN',
            'name' => 'Morning',
            'start_time' => '08:00',
            'end_time' => '16:00',
            'duration_minutes' => 480,
            'is_active' => true,
        ]);

        $period = RosterPeriod::query()->create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'name' => 'Publishing Test Period',
            'start_date' => '2026-10-05',
            'end_date' => '2026-10-11',
            'status' => 'draft',
            'created_by' => $manager->id,
        ]);

        // Assignment with inactive employee -> Critical Violation
        $badAssignment = RosterAssignment::query()->create([
            'tenant_id' => $tenant->id,
            'roster_period_id' => $period->id,
            'employee_id' => $empInactive->id,
            'roster_date' => '2026-10-05',
            'shift_definition_id' => $shift->id,
            'assignment_status' => 'scheduled',
        ]);

        $publicationService = app(SchedulePublicationService::class);

        // 1. Publishing must fail with ValidationException
        $blocked = false;
        try {
            $publicationService->publish($period, $manager);
        } catch (ValidationException $e) {
            $blocked = true;
            $this->assertArrayHasKey('schedule_validation', $e->errors());
        }
        $this->assertTrue($blocked, 'Publishing should have been blocked due to hard constraint violation.');

        // 2. Fix assignment with active employee -> Publishing succeeds
        $badAssignment->update(['employee_id' => $empActive->id]);

        $published = $publicationService->publish($period, $manager);
        $this->assertEquals('published', $published->status);
        $this->assertEquals(2, $published->version);
        $this->assertTrue($badAssignment->fresh()->is_published);

        // 3. Locking Period
        $locked = $publicationService->lock($published, $manager);
        $this->assertTrue($locked->is_locked);
        $this->assertEquals('locked', $locked->status);
    }

    public function test_post_publication_change_logging(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $manager = User::factory()->create(['tenant_id' => $tenant->id]);
        $employee = $this->createEmployee($tenant->id, $company->id);

        $shiftA = ShiftDefinition::query()->create([
            'tenant_id' => $tenant->id,
            'shift_code' => 'SHIFT-A',
            'name' => 'Shift Alpha',
            'start_time' => '08:00',
            'end_time' => '16:00',
            'duration_minutes' => 480,
            'is_active' => true,
        ]);

        $shiftB = ShiftDefinition::query()->create([
            'tenant_id' => $tenant->id,
            'shift_code' => 'SHIFT-B',
            'name' => 'Shift Beta',
            'start_time' => '16:00',
            'end_time' => '00:00',
            'duration_minutes' => 480,
            'is_active' => true,
        ]);

        $period = RosterPeriod::query()->create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'name' => 'Audited Period',
            'start_date' => '2026-10-12',
            'end_date' => '2026-10-18',
            'status' => 'published',
            'created_by' => $manager->id,
        ]);

        $assignment = RosterAssignment::query()->create([
            'tenant_id' => $tenant->id,
            'roster_period_id' => $period->id,
            'employee_id' => $employee->id,
            'roster_date' => '2026-10-12',
            'shift_definition_id' => $shiftA->id,
            'assignment_status' => 'scheduled',
            'is_published' => true,
        ]);

        $publicationService = app(SchedulePublicationService::class);

        // Record late change
        $changeLog = $publicationService->recordChange(
            assignment: $assignment,
            newShift: $shiftB,
            newDate: '2026-10-12',
            reason: 'Operational emergency reassignment',
            changedBy: $manager
        );

        $this->assertEquals($shiftA->id, $changeLog->previous_shift_id);
        $this->assertEquals($shiftB->id, $changeLog->new_shift_id);
        $this->assertEquals($shiftB->id, $assignment->fresh()->shift_definition_id);
        $this->assertDatabaseHas('hcm_schedule_change_logs', [
            'id' => $changeLog->id,
            'reason' => 'Operational emergency reassignment',
        ]);
    }

    protected function createEmployee(string $tenantId, string $companyId, array $attributes = []): Employee
    {
        return Employee::query()->create(array_merge([
            'tenant_id' => $tenantId,
            'company_id' => $companyId,
            'employee_code' => 'EMP-' . uniqid(),
            'employee_number' => 'EMP-' . rand(1000, 9999),
            'first_name' => 'Hamza',
            'last_name' => 'Ali',
            'official_email' => 'hamza.' . uniqid() . '@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ], $attributes));
    }
}
