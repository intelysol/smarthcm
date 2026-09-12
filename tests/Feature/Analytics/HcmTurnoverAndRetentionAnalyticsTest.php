<?php

namespace Tests\Feature\Analytics;

use App\Domains\Analytics\Services\HcmWorkforceAnalyticsService;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\BusinessUnit;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Department;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HcmTurnoverAndRetentionAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_turnover_rates_and_voluntary_exit_breakdown(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $bu = BusinessUnit::create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'name' => 'HQ Unit', 'code' => 'BU-01']);
        $dept = Department::create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'business_unit_id' => $bu->id, 'department_name' => 'Operations', 'department_code' => 'OPS']);

        // 8 Active Employees
        for ($i = 1; $i <= 8; $i++) {
            Employee::create([
                'tenant_id' => $tenant->id,
                'company_id' => $company->id,
                'department_id' => $dept->id,
                'employee_code' => "EMP-ACT-{$i}",
                'employee_number' => "EMP-ACT-{$i}",
                'first_name' => "Active",
                'last_name' => "User {$i}",
                'official_email' => "active{$i}@example.com",
                'employment_status' => 'active',
                'joining_date' => '2025-01-01',
            ]);
        }

        // 2 Voluntary Exits in Q1
        Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'department_id' => $dept->id,
            'employee_code' => "EMP-EXIT-1",
            'employee_number' => "EMP-EXIT-1",
            'first_name' => "Voluntary",
            'last_name' => "Exit 1",
            'official_email' => "exit1@example.com",
            'employment_status' => 'terminated',
            'exit_type' => 'voluntary',
            'joining_date' => '2025-01-01',
            'termination_date' => '2026-02-15',
        ]);

        Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'department_id' => $dept->id,
            'employee_code' => "EMP-EXIT-2",
            'employee_number' => "EMP-EXIT-2",
            'first_name' => "Voluntary",
            'last_name' => "Exit 2",
            'official_email' => "exit2@example.com",
            'employment_status' => 'terminated',
            'exit_type' => 'voluntary',
            'joining_date' => '2025-01-01',
            'termination_date' => '2026-03-10',
        ]);

        $analyticsService = app(HcmWorkforceAnalyticsService::class);
        $turnover = $analyticsService->getTurnoverAnalytics($tenant->id, '2026-01-01', '2026-03-31');

        $this->assertEquals(2, $turnover['total_exits']);
        $this->assertEquals(2, $turnover['voluntary_exits']);
        $this->assertEquals(0, $turnover['involuntary_exits']);
        $this->assertGreaterThan(0, $turnover['turnover_rate_percent']);
        $this->assertGreaterThan(0, $turnover['voluntary_turnover_rate_percent']);
    }
}
