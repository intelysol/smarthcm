<?php

namespace Tests\Feature\Attendance;

use App\Domains\Attendance\Models\AttendanceDevice;
use App\Domains\Attendance\Models\AttendanceEvent;
use App\Domains\Attendance\Models\AttendanceRawEvent;
use App\Domains\Attendance\Services\AttendanceDeviceService;
use App\Domains\Attendance\Services\AttendanceNormalizer;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use Database\Seeders\AttendanceDefaultDataSeeder;
use Database\Seeders\AttendancePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeviceIngestionAndNormalizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AttendancePermissionSeeder::class);
    }

    public function test_device_ingestion_immutable_ledger_and_idempotency(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $employee = $this->createEmployee($tenant->id, $company->id, [
            'employee_number' => 'EMP-7700',
        ]);

        $device = AttendanceDevice::query()->create([
            'tenant_id' => $tenant->id,
            'device_code' => 'DEV-ZK-01',
            'name' => 'HQ Gate ZKTeco Terminal',
            'vendor' => 'zkteco',
            'connector_type' => 'zkteco',
            'ip_address' => '192.168.1.50',
            'port' => 4370,
            'timezone' => 'UTC',
            'is_active' => true,
        ]);

        $deviceService = app(AttendanceDeviceService::class);

        $events = [
            [
                'employee_identifier' => 'EMP-7700',
                'timestamp' => '2026-09-07 08:02:15',
                'event_type' => 'IN',
                'device_event_id' => 'LOG-1001',
            ],
            [
                'employee_identifier' => 'EMP-7700',
                'timestamp' => '2026-09-07 17:05:30',
                'event_type' => 'OUT',
                'device_event_id' => 'LOG-1002',
            ],
        ];

        // First Ingestion
        $result1 = $deviceService->ingestRawEvents($tenant->id, $device, $events);
        $this->assertEquals(2, $result1['imported']);
        $this->assertEquals(0, $result1['duplicates']);

        // Verify Immutable Raw Events Ledger
        $this->assertEquals(2, AttendanceRawEvent::query()->where('tenant_id', $tenant->id)->count());
        $this->assertEquals(2, AttendanceEvent::query()->where('tenant_id', $tenant->id)->count());

        // Duplicate Ingestion Attempt (Same device events)
        $result2 = $deviceService->ingestRawEvents($tenant->id, $device, $events);
        $this->assertEquals(0, $result2['imported']);
        $this->assertEquals(2, $result2['duplicates']);

        // Ledger size remains strictly 2 (No duplication)
        $this->assertEquals(2, AttendanceRawEvent::query()->where('tenant_id', $tenant->id)->count());
        $this->assertEquals(2, AttendanceEvent::query()->where('tenant_id', $tenant->id)->count());
    }

    public function test_normalization_maps_employee_and_event_types(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $employee = $this->createEmployee($tenant->id, $company->id, [
            'employee_code' => 'CARD-9911',
        ]);

        $normalizer = app(AttendanceNormalizer::class);

        $rawIn = AttendanceRawEvent::query()->create([
            'tenant_id' => $tenant->id,
            'employee_device_identifier' => 'CARD-9911',
            'event_timestamp' => '2026-09-07 08:00:00',
            'event_type' => 'IN',
            'idempotency_key' => 'RAW_KEY_1',
            'is_processed' => false,
        ]);

        $event = $normalizer->normalizeRawEvent($rawIn);

        $this->assertNotNull($event);
        $this->assertEquals($employee->id, $event->employee_id);
        $this->assertEquals('check_in', $event->event_type);
        $this->assertEquals('2026-09-07', $event->local_date->toDateString());
        $this->assertTrue($rawIn->fresh()->is_processed);
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
