<?php

namespace Tests\Feature\AttendanceTime;

use App\Domains\Attendance\Models\AttendanceEvent;
use App\Domains\Attendance\Models\AttendanceRawEvent;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Database\Seeders\AttendancePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IdempotentRawPunchNormalizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AttendancePermissionSeeder::class);
    }

    public function test_raw_punch_ingestion_and_idempotency(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $employee = Employee::query()->create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-101',
            'employee_number' => '100101',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'official_email' => 'john.doe@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ]);

        $payload = [
            'employee_id' => $employee->id,
            'event_type' => 'IN',
            'event_timestamp' => '2026-10-01T08:58:00Z',
            'source' => 'biometric_zkteco',
            'idempotency_key' => 'IDEMP-PUNCH-001',
        ];

        // First ingestion: 201 created
        $response1 = $this->actingAs($user)->postJson('/api/v1/hcm/time/punch', $payload);
        $response1->assertStatus(201);
        $response1->assertJson(['is_duplicate' => false]);

        $this->assertDatabaseHas('attendance_raw_events', [
            'tenant_id' => $tenant->id,
            'idempotency_key' => 'IDEMP-PUNCH-001',
        ]);

        $this->assertDatabaseHas('attendance_events', [
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'event_type' => 'check_in',
        ]);

        // Duplicate ingestion with same idempotency key: 200 idempotent response
        $response2 = $this->actingAs($user)->postJson('/api/v1/hcm/time/punch', $payload);
        $response2->assertStatus(200);
        $response2->assertJson(['is_duplicate' => true]);

        // Ensure only 1 raw event exists
        $this->assertEquals(1, AttendanceRawEvent::where('idempotency_key', 'IDEMP-PUNCH-001')->count());
    }
}