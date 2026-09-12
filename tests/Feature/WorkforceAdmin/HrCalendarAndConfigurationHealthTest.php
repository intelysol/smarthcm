<?php

namespace Tests\Feature\WorkforceAdmin;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Domains\WorkforceAdmin\Models\OpsCalendarEvent;
use App\Domains\WorkforceAdmin\Services\ConfigurationHealthService;
use App\Domains\WorkforceAdmin\Services\HrCalendarService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HrCalendarAndConfigurationHealthTest extends TestCase
{
    use RefreshDatabase;

    public function test_hr_calendar_sync_and_configuration_diagnostics(): void
    {
        Carbon::setTestNow('2026-09-01 10:00:00');

        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-CAL-001',
            'employee_number' => 'EMP-CAL-001',
            'first_name' => 'Toby',
            'last_name' => 'Flenderson',
            'official_email' => 'toby@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-09-15',
        ]);

        // 1. Sync Calendar Events
        $calendarService = app(HrCalendarService::class);
        $count = $calendarService->syncCalendarEvents($tenant->id);

        $this->assertEquals(1, $count);
        $event = OpsCalendarEvent::where('tenant_id', $tenant->id)
            ->where('event_type', 'new_hire_joining')
            ->where('source_entity_id', $employee->id)
            ->first();

        $this->assertNotNull($event);
        $this->assertEquals('2026-09-15', $event->event_date->format('Y-m-d'));

        // 2. Configuration Health Diagnostics
        $healthService = app(ConfigurationHealthService::class);
        $diagnostics = $healthService->runDiagnostics($tenant->id);

        $this->assertCount(3, $diagnostics);
        $this->assertDatabaseHas('hcm_ops_configuration_health_checks', [
            'tenant_id' => $tenant->id,
            'config_category' => 'workflows',
            'health_status' => 'healthy',
        ]);
    }
}
