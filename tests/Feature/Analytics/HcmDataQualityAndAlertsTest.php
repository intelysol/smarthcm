<?php

namespace Tests\Feature\Analytics;

use App\Domains\Analytics\Models\HcmAnalyticsAlert;
use App\Domains\Analytics\Models\HcmAnalyticsAlertSubscription;
use App\Domains\Analytics\Models\HcmAnalyticsDataQualityCheck;
use App\Domains\Analytics\Models\HcmAnalyticsMetric;
use App\Domains\Analytics\Services\HcmAnalyticsAlertService;
use App\Domains\Analytics\Services\HcmDataQualityService;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HcmDataQualityAndAlertsTest extends TestCase
{
    use RefreshDatabase;

    public function test_data_quality_integrity_scoring_and_metric_alerts(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        // 1. Data Quality Test: Create Employee with missing department
        Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'department_id' => null, // Missing department!
            'employee_code' => 'EMP-DQ-1',
            'employee_number' => 'EMP-DQ-1',
            'first_name' => 'Orphan',
            'last_name' => 'Employee',
            'official_email' => 'orphan@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ]);

        HcmAnalyticsDataQualityCheck::create([
            'tenant_id' => $tenant->id,
            'code' => 'DQ_MISSING_DEPT',
            'name' => 'Employees Without Department',
            'domain_module' => 'core_hr',
            'severity' => 'error',
            'check_type' => 'missing_department',
            'is_active' => true,
        ]);

        $qualityService = app(HcmDataQualityService::class);
        $qualitySummary = $qualityService->runDataQualityValidation($tenant->id);

        $this->assertEquals(0.00, $qualitySummary['overall_data_quality_score']);
        $this->assertEquals(1, $qualitySummary['results'][0]['failed_records']);

        // 2. Metric Alert Test
        $metric = HcmAnalyticsMetric::create([
            'tenant_id' => $tenant->id,
            'code' => 'HCM_TURNOVER_RATE',
            'name' => 'Turnover Rate',
            'category' => 'turnover',
            'unit' => 'percentage',
        ]);

        $alert = HcmAnalyticsAlert::create([
            'tenant_id' => $tenant->id,
            'hcm_analytics_metric_id' => $metric->id,
            'title' => 'High Turnover Warning',
            'comparison_operator' => '>',
            'threshold_value' => 10.0,
            'severity' => 'warning',
            'is_active' => true,
        ]);

        $sub = HcmAnalyticsAlertSubscription::create([
            'tenant_id' => $tenant->id,
            'hcm_analytics_alert_id' => $alert->id,
            'user_id' => $user->id,
            'delivery_channel' => 'in_app',
        ]);

        $alertService = app(HcmAnalyticsAlertService::class);
        
        // Value 8.0 should NOT trigger threshold 10.0
        $noTriggers = $alertService->evaluateAlerts($tenant->id, ['HCM_TURNOVER_RATE' => 8.0]);
        $this->assertCount(0, $noTriggers);

        // Value 12.5 SHOULD trigger threshold 10.0
        $triggers = $alertService->evaluateAlerts($tenant->id, ['HCM_TURNOVER_RATE' => 12.5]);
        $this->assertCount(1, $triggers);
        $this->assertEquals('High Turnover Warning', $triggers[0]['title']);
    }
}
