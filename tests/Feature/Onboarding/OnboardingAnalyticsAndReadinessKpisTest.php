<?php

namespace Tests\Feature\Onboarding;

use App\Domains\Employee\Models\Employee;
use App\Domains\Onboarding\Enums\OnboardingCaseStatus;
use App\Domains\Onboarding\Enums\OnboardingTaskStatus;
use App\Domains\Onboarding\Models\HcmOnboardingCase;
use App\Domains\Onboarding\Models\HcmOnboardingCaseTask;
use App\Domains\Onboarding\Models\HcmOnboardingTemplate;
use App\Domains\Onboarding\Models\HcmOnboardingTemplateVersion;
use App\Domains\Onboarding\Services\OnboardingAnalyticsService;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnboardingAnalyticsAndReadinessKpisTest extends TestCase
{
    use RefreshDatabase;

    public function test_readiness_metrics_and_analytics_kpis(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $employee1 = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-K1',
            'employee_number' => 'EMP-K1',
            'first_name' => 'Clark',
            'last_name' => 'Kent',
            'official_email' => 'clark@example.com',
            'joining_date' => now()->toDateString(),
            'employment_status' => 'active',
        ]);

        $template = HcmOnboardingTemplate::create(['tenant_id' => $tenant->id, 'name' => 'T', 'code' => 'T-KPI']);
        $version = HcmOnboardingTemplateVersion::create(['tenant_id' => $tenant->id, 'template_id' => $template->id, 'version_number' => 1]);

        $case1 = HcmOnboardingCase::create([
            'tenant_id' => $tenant->id,
            'case_number' => 'ONB-K1',
            'employee_id' => $employee1->id,
            'template_version_id' => $version->id,
            'start_date' => now()->toDateString(),
            'status' => OnboardingCaseStatus::COMPLETED->value,
            'completion_percentage' => 100.00,
        ]);

        // Overdue task
        HcmOnboardingCaseTask::create([
            'tenant_id' => $tenant->id,
            'case_id' => $case1->id,
            'title' => 'Overdue Item',
            'status' => OnboardingTaskStatus::PENDING->value,
            'due_date' => now()->subDays(5)->toDateString(),
        ]);

        // Blocked task
        HcmOnboardingCaseTask::create([
            'tenant_id' => $tenant->id,
            'case_id' => $case1->id,
            'title' => 'Blocked Item',
            'status' => OnboardingTaskStatus::BLOCKED->value,
        ]);

        $service = new OnboardingAnalyticsService();
        $kpis = $service->getOnboardingKpis($tenant->id);

        $this->assertEquals(1, $kpis['total_cases']);
        $this->assertEquals(1, $kpis['completed_cases']);
        $this->assertEquals(100.0, $kpis['completion_rate_pct']);
        $this->assertEquals(1, $kpis['overdue_tasks']);
        $this->assertEquals(1, $kpis['blocked_tasks']);
    }
}
