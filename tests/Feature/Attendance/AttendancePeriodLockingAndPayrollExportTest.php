<?php

namespace Tests\Feature\Attendance;

use App\Domains\Attendance\Enums\PeriodStatus;
use App\Domains\Attendance\Enums\TimesheetStatus;
use App\Domains\Attendance\Models\AttendancePeriod;
use App\Domains\Attendance\Services\AttendanceDeviceService;
use App\Domains\Attendance\Services\AttendanceExportService;
use App\Domains\Attendance\Services\AttendancePeriodService;
use App\Domains\Attendance\Services\AttendanceProcessor;
use App\Domains\Attendance\Services\TimesheetService;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Database\Seeders\AttendanceDefaultDataSeeder;
use Database\Seeders\AttendancePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AttendancePeriodLockingAndPayrollExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AttendancePermissionSeeder::class);
    }

    public function test_period_locking_prevents_retroactive_changes_and_allows_audited_reopening(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $hrAdmin = User::factory()->create(['tenant_id' => $tenant->id]);

        $employee = $this->createEmployee($tenant->id, $company->id, [
            'employee_number' => 'EMP-LOCK',
        ]);

        $seeder = new AttendanceDefaultDataSeeder();
        $seeder->seedTenantData($tenant->id);

        $periodService = app(AttendancePeriodService::class);
        $processor = app(AttendanceProcessor::class);

        // 1. Create and Lock Period
        $period = $periodService->createPeriod($tenant->id, [
            'company_id' => $company->id,
            'period_name' => 'August 2026 Monthly Attendance',
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-31',
        ], $hrAdmin);

        $this->assertEquals(PeriodStatus::OPEN->value, $period->status);

        $periodService->lockPeriod($period, $hrAdmin);
        $this->assertEquals(PeriodStatus::LOCKED->value, $period->fresh()->status);
        $this->assertTrue($period->fresh()->isLocked());

        // 2. Attempting to process attendance for a locked date must throw ValidationException
        $this->expectException(ValidationException::class);
        $processor->processEmployeeDate($employee, '2026-08-15');
    }

    public function test_payroll_export_payload_structure(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $hrAdmin = User::factory()->create(['tenant_id' => $tenant->id]);

        $employee = $this->createEmployee($tenant->id, $company->id, [
            'employee_number' => 'EMP-PAY',
            'first_name' => 'Alice',
            'last_name' => 'Smith',
        ]);

        $seeder = new AttendanceDefaultDataSeeder();
        $seeder->seedTenantData($tenant->id);

        $deviceService = app(AttendanceDeviceService::class);
        $processor = app(AttendanceProcessor::class);
        $timesheetService = app(TimesheetService::class);
        $exportService = app(AttendanceExportService::class);

        $deviceService->ingestRawEvents($tenant->id, null, [
            ['employee_identifier' => 'EMP-PAY', 'timestamp' => '2026-09-07 08:00:00', 'event_type' => 'IN'],
            ['employee_identifier' => 'EMP-PAY', 'timestamp' => '2026-09-07 17:00:00', 'event_type' => 'OUT'],
        ]);

        $processor->processEmployeeDate($employee, '2026-09-07');
        $timesheet = $timesheetService->generateTimesheet($employee, '2026-09-01', '2026-09-30', 'monthly', $hrAdmin);
        $timesheetService->approveTimesheet($timesheet, $hrAdmin);

        // Generate Payroll Contract Payload
        $payload = $exportService->generatePayrollExportPayload($tenant->id, '2026-09-01', '2026-09-30');

        $this->assertCount(1, $payload);
        $entry = $payload[0];
        $this->assertEquals('EMP-PAY', $entry['employee_number']);
        $this->assertEquals('Alice Smith', $entry['employee_name']);
        $this->assertEquals(480, $entry['regular_minutes']);
        $this->assertEquals(8.0, $entry['regular_hours']);
        $this->assertEquals(0, $entry['approved_overtime_minutes']);

        // Verify CSV generation
        $csv = $exportService->generateCsv($payload);
        $this->assertStringContainsString('EMP-PAY', $csv);
        $this->assertStringContainsString('Alice Smith', $csv);
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
