<?php

namespace Tests\Feature\Analytics;

use App\Domains\Analytics\Services\HcmAiAnalyticsService;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Branch;
use App\Domains\Organization\Models\BusinessUnit;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Department;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HcmAiAnalyticsAssistantTest extends TestCase
{
    use RefreshDatabase;

    public function test_ai_analytics_natural_language_intent_resolution_and_explanation(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $bu = BusinessUnit::create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'name' => 'HQ Unit', 'code' => 'BU-01']);
        $dept = Department::create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'business_unit_id' => $bu->id, 'department_name' => 'Engineering', 'department_code' => 'ENG']);
        $branch = Branch::create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'branch_name' => 'HQ', 'branch_code' => 'BR-01']);

        Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'department_id' => $dept->id,
            'branch_id' => $branch->id,
            'employee_code' => 'EMP-AI-1',
            'employee_number' => 'EMP-AI-1',
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'official_email' => 'ada@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ]);

        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $aiService = app(HcmAiAnalyticsService::class);

        // 1. Ask about headcount
        $headcountResponse = $aiService->processNaturalLanguageQuery($tenant->id, 'How many active employees do we have right now?', $user);
        $this->assertEquals('HCM_HEADCOUNT_ACTIVE', $headcountResponse['resolved_metric_code']);
        $this->assertTrue($headcountResponse['is_advisory_only']);
        $this->assertStringContainsString('Total headcount stands at 1', $headcountResponse['ai_explanation']);

        // 2. Ask about turnover
        $turnoverResponse = $aiService->processNaturalLanguageQuery($tenant->id, 'Why did employee turnover change this quarter?', $user);
        $this->assertEquals('HCM_TURNOVER_RATE', $turnoverResponse['resolved_metric_code']);
        $this->assertStringContainsString('turnover rate is currently', $turnoverResponse['ai_explanation']);
    }
}
