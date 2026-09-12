<?php

namespace Tests\Feature\Attendance;

use App\Domains\Attendance\Models\AttendanceDevice;
use App\Domains\Attendance\Models\AttendanceSession;
use App\Domains\Attendance\Models\ShiftDefinition;
use App\Domains\Attendance\Services\AttendanceDeviceService;
use App\Domains\Attendance\Services\AttendanceProcessor;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use Database\Seeders\AttendanceDefaultDataSeeder;
use Database\Seeders\AttendancePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceProcessingAndMultiPunchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AttendancePermissionSeeder::class);
    }

    public function test_standard_duty_processing_with_break_deduction(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $employee = $this->createEmployee($tenant->id, $company->id, [
            'employee_number' => 'EMP-100',
        ]);

        $seeder = new AttendanceDefaultDataSeeder();
        $seeder->seedTenantData($tenant->id);

        $deviceService = app(AttendanceDeviceService::class);
        $processor = app(AttendanceProcessor::class);

        // Employee punches: Check In (08:00), Lunch Out (12:30), Lunch In (13:30), Check Out (17:00)
        $deviceService->ingestRawEvents($tenant->id, null, [
            ['employee_identifier' => 'EMP-100', 'timestamp' => '2026-09-07 08:00:00', 'event_type' => 'IN'],
            ['employee_identifier' => 'EMP-100', 'timestamp' => '2026-09-07 12:30:00', 'event_type' => 'OUT'],
            ['employee_identifier' => 'EMP-100', 'timestamp' => '2026-09-07 13:30:00', 'event_type' => 'IN'],
            ['employee_identifier' => 'EMP-100', 'timestamp' => '2026-09-07 17:00:00', 'event_type' => 'OUT'],
        ]);

        $session = $processor->processEmployeeDate($employee, '2026-09-07');

        $this->assertNotNull($session);
        $this->assertEquals('present', $session->status);
        $this->assertEquals(540, $session->gross_duration_minutes);
        $this->assertEquals(60, $session->total_break_minutes);
        $this->assertEquals(480, $session->net_worked_minutes);
        $this->assertEquals(0, $session->late_minutes);
        $this->assertEquals(0, $session->early_departure_minutes);
        $this->assertEquals(0, $session->overtime_minutes);
    }

    public function test_tardiness_and_overtime_calculation(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $employee = $this->createEmployee($tenant->id, $company->id, [
            'employee_number' => 'EMP-200',
        ]);

        $seeder = new AttendanceDefaultDataSeeder();
        $seeder->seedTenantData($tenant->id);

        $deviceService = app(AttendanceDeviceService::class);
        $processor = app(AttendanceProcessor::class);

        // Shift starts 08:00 with 15m grace. Arrives 08:35 (35m late). Departs 18:35.
        $deviceService->ingestRawEvents($tenant->id, null, [
            ['employee_identifier' => 'EMP-200', 'timestamp' => '2026-09-07 08:35:00', 'event_type' => 'IN'],
            ['employee_identifier' => 'EMP-200', 'timestamp' => '2026-09-07 12:30:00', 'event_type' => 'OUT'],
            ['employee_identifier' => 'EMP-200', 'timestamp' => '2026-09-07 13:30:00', 'event_type' => 'IN'],
            ['employee_identifier' => 'EMP-200', 'timestamp' => '2026-09-07 18:35:00', 'event_type' => 'OUT'],
        ]);

        $session = $processor->processEmployeeDate($employee, '2026-09-07');

        $this->assertEquals(35, $session->late_minutes);
        $this->assertEquals(60, $session->overtime_minutes);
        $this->assertEquals(480, $session->regular_minutes);
        $this->assertEquals(540, $session->net_worked_minutes);
    }

    public function test_overnight_cross_midnight_shift_processing(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $employee = $this->createEmployee($tenant->id, $company->id, [
            'employee_number' => 'EMP-NIGHT',
        ]);

        $seeder = new AttendanceDefaultDataSeeder();
        $seeder->seedTenantData($tenant->id);

        $nightShift = ShiftDefinition::query()->where('tenant_id', $tenant->id)->where('shift_code', 'SHIFT-NIGHT')->first();
        $rosterService = app(\App\Domains\Attendance\Services\RosterService::class);
        $period = $rosterService->createPeriod($tenant->id, ['name' => 'Night Shift Period', 'start_date' => '2026-09-01', 'end_date' => '2026-09-14']);
        $rosterService->assignShift($period, $employee, '2026-09-07', $nightShift);

        $deviceService = app(AttendanceDeviceService::class);
        $processor = app(AttendanceProcessor::class);

        // Overnight shift: 22:00 (Sep 07) to 06:00 (Sep 08)
        $deviceService->ingestRawEvents($tenant->id, null, [
            ['employee_identifier' => 'EMP-NIGHT', 'timestamp' => '2026-09-07 21:55:00', 'event_type' => 'IN'],
            ['employee_identifier' => 'EMP-NIGHT', 'timestamp' => '2026-09-08 06:05:00', 'event_type' => 'OUT'],
        ]);

        $session = $processor->processEmployeeDate($employee, '2026-09-07');

        $this->assertNotNull($session);
        $this->assertTrue($session->is_overnight);
        $this->assertEquals(490, $session->net_worked_minutes);
        $this->assertEquals(0, $session->late_minutes);
        $this->assertEquals(0, $session->early_departure_minutes);
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
