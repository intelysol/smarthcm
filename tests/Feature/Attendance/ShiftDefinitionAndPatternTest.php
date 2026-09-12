<?php

namespace Tests\Feature\Attendance;

use App\Domains\Attendance\Models\ShiftDefinition;
use App\Domains\Attendance\Models\ShiftPattern;
use App\Domains\Attendance\Services\ShiftResolver;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use Database\Seeders\AttendanceDefaultDataSeeder;
use Database\Seeders\AttendancePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShiftDefinitionAndPatternTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AttendancePermissionSeeder::class);
    }

    public function test_shift_types_and_breaks_structure(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $seeder = new AttendanceDefaultDataSeeder();
        $seeder->seedTenantData($tenant->id);

        $morningShift = ShiftDefinition::query()
            ->where('tenant_id', $tenant->id)
            ->where('shift_code', 'SHIFT-MORN')
            ->with('breaks')
            ->first();

        $this->assertNotNull($morningShift);
        $this->assertEquals(480, $morningShift->duration_minutes);
        $this->assertEquals('normal', $morningShift->shift_type);
        $this->assertCount(1, $morningShift->breaks);
        $this->assertEquals(60, $morningShift->breaks->first()->duration_minutes);

        $nightShift = ShiftDefinition::query()
            ->where('tenant_id', $tenant->id)
            ->where('shift_code', 'SHIFT-NIGHT')
            ->first();

        $this->assertNotNull($nightShift);
        $this->assertTrue($nightShift->is_night_shift);
        $this->assertEquals('overnight', $nightShift->shift_type);

        $flexShift = ShiftDefinition::query()
            ->where('tenant_id', $tenant->id)
            ->where('shift_code', 'SHIFT-FLEX')
            ->first();

        $this->assertNotNull($flexShift);
        $this->assertTrue($flexShift->is_flexible);
        $this->assertEquals('10:00', $flexShift->core_start_time);
        $this->assertEquals('15:00', $flexShift->core_end_time);
    }

    public function test_shift_pattern_resolution_across_cycle(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $employee = $this->createEmployee($tenant->id, $company->id);

        $seeder = new AttendanceDefaultDataSeeder();
        $seeder->seedTenantData($tenant->id);

        $shiftResolver = app(ShiftResolver::class);

        // Monday 2026-09-07 -> Should resolve to Morning Shift
        $mon = $shiftResolver->resolvePlannedShift($employee, '2026-09-07');
        $this->assertTrue($mon['is_working_day']);
        $this->assertEquals('SHIFT-MORN', $mon['shift']->shift_code);

        // Sunday 2026-09-06 -> Should resolve to off-day
        $sun = $shiftResolver->resolvePlannedShift($employee, '2026-09-06');
        $this->assertFalse($sun['is_working_day']);
        $this->assertNull($sun['shift']);
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
