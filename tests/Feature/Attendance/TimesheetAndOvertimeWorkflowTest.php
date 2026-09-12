<?php

namespace Tests\Feature\Attendance;

use App\Domains\Attendance\Enums\OvertimeStatus;
use App\Domains\Attendance\Enums\TimesheetStatus;
use App\Domains\Attendance\Models\Timesheet;
use App\Domains\Attendance\Services\AttendanceDeviceService;
use App\Domains\Attendance\Services\AttendanceProcessor;
use App\Domains\Attendance\Services\OvertimeService;
use App\Domains\Attendance\Services\TimesheetService;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Database\Seeders\AttendanceDefaultDataSeeder;
use Database\Seeders\AttendancePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimesheetAndOvertimeWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AttendancePermissionSeeder::class);
    }

    public function test_timesheet_generation_and_approval_workflow(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $manager = User::factory()->create(['tenant_id' => $tenant->id]);

        $employee = $this->createEmployee($tenant->id, $company->id, [
            'user_id' => $user->id,
            'employee_number' => 'EMP-TS',
        ]);

        $seeder = new AttendanceDefaultDataSeeder();
        $seeder->seedTenantData($tenant->id);

        $deviceService = app(AttendanceDeviceService::class);
        $processor = app(AttendanceProcessor::class);
        $timesheetService = app(TimesheetService::class);

        // Record attendance for 2 days (Mon Sep 07 and Tue Sep 08)
        $deviceService->ingestRawEvents($tenant->id, null, [
            ['employee_identifier' => 'EMP-TS', 'timestamp' => '2026-09-07 08:00:00', 'event_type' => 'IN'],
            ['employee_identifier' => 'EMP-TS', 'timestamp' => '2026-09-07 17:00:00', 'event_type' => 'OUT'],
            ['employee_identifier' => 'EMP-TS', 'timestamp' => '2026-09-08 08:00:00', 'event_type' => 'IN'],
            ['employee_identifier' => 'EMP-TS', 'timestamp' => '2026-09-08 17:00:00', 'event_type' => 'OUT'],
        ]);

        $processor->processEmployeeDate($employee, '2026-09-07');
        $processor->processEmployeeDate($employee, '2026-09-08');

        // 1. Generate Timesheet for the week
        $timesheet = $timesheetService->generateTimesheet($employee, '2026-09-07', '2026-09-13', 'weekly', $user);

        $this->assertNotNull($timesheet);
        $this->assertEquals(TimesheetStatus::DRAFT->value, $timesheet->status);
        $this->assertEquals(1080, $timesheet->total_worked_minutes); // 540 + 540
        $this->assertEquals(7, $timesheet->entries->count());

        // 2. Submit Timesheet
        $timesheetService->submitTimesheet($timesheet);
        $this->assertEquals(TimesheetStatus::SUBMITTED->value, $timesheet->fresh()->status);

        // 3. Manager Approves Timesheet
        $timesheetService->approveTimesheet($timesheet, $manager);
        $this->assertEquals(TimesheetStatus::HR_APPROVED->value, $timesheet->fresh()->status);
        $this->assertEquals($manager->id, $timesheet->fresh()->approved_by);
    }

    public function test_overtime_request_and_signoff(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $manager = User::factory()->create(['tenant_id' => $tenant->id]);

        $employee = $this->createEmployee($tenant->id, $company->id, [
            'user_id' => $user->id,
            'employee_number' => 'EMP-OT',
        ]);

        $seeder = new AttendanceDefaultDataSeeder();
        $seeder->seedTenantData($tenant->id);

        $deviceService = app(AttendanceDeviceService::class);
        $processor = app(AttendanceProcessor::class);
        $otService = app(OvertimeService::class);

        // 2 hours overtime worked (08:00 to 18:00 = 600m - 480m scheduled = 120m overtime)
        $deviceService->ingestRawEvents($tenant->id, null, [
            ['employee_identifier' => 'EMP-OT', 'timestamp' => '2026-09-07 08:00:00', 'event_type' => 'IN'],
            ['employee_identifier' => 'EMP-OT', 'timestamp' => '2026-09-07 18:00:00', 'event_type' => 'OUT'],
        ]);

        $session = $processor->processEmployeeDate($employee, '2026-09-07');
        $this->assertEquals(120, $session->overtime_minutes);

        // 1. Submit Overtime Request
        $otRequest = $otService->requestOvertime($employee, [
            'overtime_date' => '2026-09-07',
            'requested_overtime_minutes' => 120,
            'overtime_type' => 'regular_ot',
            'reason' => 'Quarter-end data migration support.',
        ], $user);

        $this->assertEquals(OvertimeStatus::PENDING->value, $otRequest->status);

        // 2. Manager Approves Overtime
        $otService->approveOvertime($otRequest, $manager, 120);

        $this->assertEquals(OvertimeStatus::APPROVED->value, $otRequest->fresh()->status);
        $this->assertEquals(120, $session->fresh()->approved_overtime_minutes);
        $this->assertTrue($session->fresh()->is_approved);
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
