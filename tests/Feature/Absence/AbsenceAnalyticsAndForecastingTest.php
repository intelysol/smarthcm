<?php

namespace Tests\Feature\Absence;

use App\Domains\Absence\Models\HcmAbsenceEvent;
use App\Domains\Absence\Services\AbsenceAnalyticsAndForecastingService;
use App\Domains\Attendance\Models\AttendanceSession;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use Carbon\Carbon;
use Database\Seeders\AttendancePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AbsenceAnalyticsAndForecastingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AttendancePermissionSeeder::class);
    }

    public function test_absence_rate_calculation_and_forecasting(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::query()->create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-ANL-01',
            'employee_number' => '200701',
            'first_name' => 'Zain',
            'last_name' => 'Abbas',
            'official_email' => 'zain.a@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ]);

        // Scheduled: 10 sessions x 480 mins = 4800 mins = 80 hours
        for ($i = 1; $i <= 10; $i++) {
            AttendanceSession::query()->create([
                'tenant_id' => $tenant->id,
                'employee_id' => $employee->id,
                'session_date' => sprintf('2026-11-%02d', $i),
                'scheduled_minutes' => 480,
                'gross_duration_minutes' => 480,
                'net_worked_minutes' => 480,
                'status' => 'completed',
            ]);
        }

        // Absence: 1 event of 8 hours
        HcmAbsenceEvent::create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'absence_date' => '2026-11-05',
            'duration_hours' => 8.00,
            'absence_category' => 'unplanned_sick',
            'source' => 'employee_self_service',
            'is_planned' => false,
            'status' => 'reported',
            'reported_at' => now(),
        ]);

        $service = app(AbsenceAnalyticsAndForecastingService::class);

        // 1. Rate calculation: 8h absence / 80h scheduled * 100 = 10.00%
        $rates = $service->calculateAbsenceRate(
            $tenant->id,
            Carbon::parse('2026-11-01'),
            Carbon::parse('2026-11-10')
        );

        $this->assertEquals(8.00, $rates['total_absence_hours']);
        $this->assertEquals(80.00, $rates['total_scheduled_hours']);
        $this->assertEquals(10.00, $rates['absence_rate_percent']);

        // 2. Forecast generation
        $forecast = $service->generateForecast(
            $tenant->id,
            Carbon::parse('2026-11-11'),
            Carbon::parse('2026-11-20')
        );

        $this->assertNotNull($forecast);
        $this->assertGreaterThan(0, $forecast->projected_absence_hours);
        $this->assertEquals(0.91, $forecast->confidence_score);
    }
}