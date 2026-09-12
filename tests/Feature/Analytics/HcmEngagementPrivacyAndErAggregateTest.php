<?php

namespace Tests\Feature\Analytics;

use App\Domains\Analytics\Services\HcmEngagementAndErAnalyticsService;
use App\Domains\Employee\Models\Employee;
use App\Domains\EmployeeRelations\Models\EmployeeRelationCase;
use App\Domains\EmployeeRelations\Models\EmployeeRelationCaseType;
use App\Domains\Engagement\Models\EngagementCampaign;
use App\Domains\Engagement\Models\EngagementResponse;
use App\Domains\Engagement\Models\EngagementSurvey;
use App\Domains\Organization\Models\BusinessUnit;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Department;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class HcmEngagementPrivacyAndErAggregateTest extends TestCase
{
    use RefreshDatabase;

    public function test_engagement_privacy_group_threshold_suppression_and_er_aggregates(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $bu = BusinessUnit::create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'name' => 'HQ Unit', 'code' => 'BU-01']);
        $dept = Department::create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'business_unit_id' => $bu->id, 'department_name' => 'HR', 'department_code' => 'HR-DEPT']);

        $service = app(HcmEngagementAndErAnalyticsService::class);

        $survey = EngagementSurvey::create([
            'tenant_id' => $tenant->id,
            'code' => 'SURV-01',
            'title' => 'Annual Pulse Survey',
            'survey_type' => 'pulse',
            'status' => 'published',
            'is_anonymous' => true,
        ]);

        $campaign = EngagementCampaign::create([
            'tenant_id' => $tenant->id,
            'survey_id' => $survey->id,
            'code' => 'CAMP-01',
            'name' => 'Q1 Pulse',
            'status' => 'active',
            'start_date' => '2026-01-01',
            'end_date' => '2026-03-31',
        ]);

        // 1. Privacy Test: 2 responses (below minimum threshold of 5)
        for ($i = 1; $i <= 2; $i++) {
            $emp = Employee::create([
                'tenant_id' => $tenant->id,
                'company_id' => $company->id,
                'department_id' => $dept->id,
                'employee_code' => "EMP-ENG-{$i}",
                'employee_number' => "EMP-ENG-{$i}",
                'first_name' => "Eng",
                'last_name' => "User {$i}",
                'official_email' => "eng{$i}@example.com",
                'employment_status' => 'active',
                'joining_date' => '2026-01-01',
            ]);

            EngagementResponse::create([
                'tenant_id' => $tenant->id,
                'survey_id' => $survey->id,
                'campaign_id' => $campaign->id,
                'employee_id' => $emp->id,
                'department_id' => $dept->id,
                'response_status' => 'submitted',
                'started_at' => now(),
                'submitted_at' => now(),
            ]);
        }

        $suppressedResult = $service->getEngagementScoreWithPrivacy($tenant->id, $dept->id);
        $this->assertTrue($suppressedResult['is_suppressed']);
        $this->assertNull($suppressedResult['enps_score']);
        $this->assertNull($suppressedResult['favorability_percent']);

        // 2. Add 3 more responses to meet minimum threshold of 5
        for ($i = 3; $i <= 5; $i++) {
            $emp = Employee::create([
                'tenant_id' => $tenant->id,
                'company_id' => $company->id,
                'department_id' => $dept->id,
                'employee_code' => "EMP-ENG-{$i}",
                'employee_number' => "EMP-ENG-{$i}",
                'first_name' => "Eng",
                'last_name' => "User {$i}",
                'official_email' => "eng{$i}@example.com",
                'employment_status' => 'active',
                'joining_date' => '2026-01-01',
            ]);

            EngagementResponse::create([
                'tenant_id' => $tenant->id,
                'survey_id' => $survey->id,
                'campaign_id' => $campaign->id,
                'employee_id' => $emp->id,
                'department_id' => $dept->id,
                'response_status' => 'submitted',
                'started_at' => now(),
                'submitted_at' => now(),
            ]);
        }

        $unsuppressedResult = $service->getEngagementScoreWithPrivacy($tenant->id, $dept->id);
        $this->assertFalse($unsuppressedResult['is_suppressed']);
        $this->assertNotNull($unsuppressedResult['enps_score']);
        $this->assertNotNull($unsuppressedResult['favorability_percent']);

        // 3. ER Aggregate Analytics Test
        $caseType = EmployeeRelationCaseType::firstOrCreate(
            ['tenant_id' => $tenant->id, 'code' => 'CT-GRIEVANCE'],
            ['name' => 'General Grievance', 'is_active' => true]
        );

        EmployeeRelationCase::create([
            'tenant_id' => $tenant->id,
            'case_number' => 'ER-001',
            'title' => 'Workplace Conflict',
            'summary' => 'Dispute regarding shift handover',
            'case_type_id' => $caseType->id,
            'severity' => 'low',
            'status' => 'open',
            'confidentiality_level' => 'confidential',
        ]);

        $erAggregates = $service->getErCaseAggregates($tenant->id);
        $this->assertEquals(1, $erAggregates['total_cases']);
        $this->assertEquals(1, $erAggregates['open_cases']);
        $this->assertArrayHasKey('by_severity', $erAggregates);
    }
}
