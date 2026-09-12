<?php

namespace Tests\Feature\AttendanceTime;

use App\Domains\Attendance\Models\Timesheet;
use App\Domains\Attendance\Models\TimesheetEntry;
use App\Domains\Attendance\Services\TimesheetProjectAllocationService;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use Database\Seeders\AttendancePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class TimesheetProjectAllocationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AttendancePermissionSeeder::class);
    }

    public function test_timesheet_project_allocation_and_summary(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $employee = Employee::query()->create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-PRJ',
            'employee_number' => '100404',
            'first_name' => 'David',
            'last_name' => 'Kim',
            'official_email' => 'david.k@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ]);

        $timesheet = Timesheet::query()->create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-31',
            'total_worked_minutes' => 480,
            'total_regular_minutes' => 480,
            'status' => 'draft',
        ]);

        $entry = TimesheetEntry::query()->create([
            'tenant_id' => $tenant->id,
            'timesheet_id' => $timesheet->id,
            'entry_date' => '2026-10-05',
            'worked_minutes' => 480,
            'regular_minutes' => 480,
        ]);

        $service = app(TimesheetProjectAllocationService::class);

        // 1. Valid allocation: 300m Project A, 180m Project B (Total 480m)
        $allocations = [
            [
                'project_code' => 'ERP-CORE',
                'task_name' => 'API Design',
                'allocated_minutes' => 300,
                'is_billable' => true,
            ],
            [
                'project_code' => 'INTERNAL-TRAINING',
                'task_name' => 'Security Workshop',
                'allocated_minutes' => 180,
                'is_billable' => false,
            ],
        ];

        $records = $service->allocateTime($tenant->id, $timesheet->id, $employee->id, '2026-10-05', $allocations, $entry->id);
        $this->assertCount(2, $records);

        // 2. Summary breakdown
        $summary = $service->getTimesheetAllocationSummary($timesheet->id);
        $this->assertEquals(480, $summary['total_allocated_minutes']);
        $this->assertEquals(300, $summary['total_billable_minutes']);
        $this->assertCount(2, $summary['by_project']);

        // 3. Exceeding allocation validation
        $this->expectException(InvalidArgumentException::class);
        $service->allocateTime($tenant->id, $timesheet->id, $employee->id, '2026-10-05', [
            ['allocated_minutes' => 600],
        ], $entry->id);
    }
}