<?php

namespace Tests\Feature\Payroll;

use App\Domains\Organization\Models\Company;
use App\Domains\Payroll\Models\PayrollCalendar;
use App\Domains\Payroll\Models\PayrollLegalEntity;
use App\Domains\Payroll\Services\PayrollCalendarService;
use App\Domains\Payroll\Services\PayrollLegalEntityService;
use App\Domains\Shared\Models\Tenant;
use Database\Seeders\PayrollDefaultDataSeeder;
use Database\Seeders\PayrollPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayrollLegalEntityAndCalendarTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PayrollPermissionSeeder::class);
    }

    public function test_legal_entity_creation_and_scoping(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $service = app(PayrollLegalEntityService::class);
        $entity = $service->createLegalEntity($tenant->id, [
            'company_id' => $company->id,
            'code' => 'LE-US-01',
            'name' => 'United States Operations LLC',
            'currency' => 'USD',
            'country' => 'USA',
            'default_pay_frequency' => 'monthly',
        ]);

        $this->assertDatabaseHas('payroll_legal_entities', [
            'id' => $entity->id,
            'code' => 'LE-US-01',
            'currency' => 'USD',
        ]);
    }

    public function test_calendar_period_date_calculations(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $calService = app(PayrollCalendarService::class);
        $calendar = $calService->createCalendar($tenant->id, [
            'code' => 'CAL-STANDARD',
            'name' => 'Monthly Cutoff 25th',
            'frequency' => 'monthly',
            'period_start_day' => 1,
            'cutoff_day_offset' => 25,
            'pay_day_offset' => 5,
        ]);

        $dates = $calService->calculatePeriodDates($calendar, 2026, 9);

        $this->assertEquals('2026-09-01', $dates['start_date']);
        $this->assertEquals('2026-09-30', $dates['end_date']);
        $this->assertEquals('2026-09-25', $dates['cutoff_date']);
        $this->assertEquals('2026-10-05', $dates['payment_date']);
    }
}
