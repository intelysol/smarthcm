<?php

namespace Tests\Feature\Attendance;

use App\Domains\Attendance\Models\Holiday;
use App\Domains\Attendance\Models\HolidayCalendar;
use App\Domains\Attendance\Models\WorkCalendar;
use App\Domains\Attendance\Services\ShiftResolver;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use Carbon\CarbonImmutable;
use Database\Seeders\AttendanceDefaultDataSeeder;
use Database\Seeders\AttendancePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkCalendarAndHolidayTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AttendancePermissionSeeder::class);
    }

    public function test_work_calendar_hierarchy_and_working_days(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $employee = $this->createEmployee($tenant->id, $company->id);

        $seeder = new AttendanceDefaultDataSeeder();
        $seeder->seedTenantData($tenant->id);

        $shiftResolver = app(ShiftResolver::class);

        // Monday (2026-09-07) -> Working Day
        $monday = CarbonImmutable::parse('2026-09-07');
        $isWorkingMon = $shiftResolver->isCalendarWorkingDay($employee, $monday);
        $this->assertTrue($isWorkingMon);

        // Sunday (2026-09-06) -> Rest Day
        $sunday = CarbonImmutable::parse('2026-09-06');
        $isWorkingSun = $shiftResolver->isCalendarWorkingDay($employee, $sunday);
        $this->assertFalse($isWorkingSun);

        // Add calendar exception making Saturday (2026-09-12) a working day
        $calendar = WorkCalendar::query()->where('tenant_id', $tenant->id)->first();
        $calendar->exceptions()->create([
            'tenant_id' => $tenant->id,
            'exception_date' => '2026-09-12',
            'is_working_day' => true,
            'custom_hours_minutes' => 300,
            'reason' => 'Emergency sprint make-up day',
        ]);

        $saturday = CarbonImmutable::parse('2026-09-12');
        $isWorkingSat = $shiftResolver->isCalendarWorkingDay($employee, $saturday);
        $this->assertTrue($isWorkingSat);
    }

    public function test_holiday_resolution(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $employee = $this->createEmployee($tenant->id, $company->id);

        $seeder = new AttendanceDefaultDataSeeder();
        $seeder->seedTenantData($tenant->id);

        $shiftResolver = app(ShiftResolver::class);

        // New Year's Day 2026-01-01 is a holiday
        $newYear = CarbonImmutable::parse('2026-01-01');
        $holiday = $shiftResolver->resolveHoliday($employee, $newYear);

        $this->assertNotNull($holiday);
        $this->assertEquals("New Year's Day", $holiday->name);

        // Normal day (2026-01-02) is not a holiday
        $regularDay = CarbonImmutable::parse('2026-01-02');
        $this->assertNull($shiftResolver->resolveHoliday($employee, $regularDay));
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
