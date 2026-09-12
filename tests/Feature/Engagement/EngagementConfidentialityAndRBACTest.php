<?php

namespace Tests\Feature\Engagement;

use App\Domains\Employee\Models\Employee;
use App\Domains\Engagement\Models\EngagementCampaign;
use App\Domains\Engagement\Models\EngagementResponse;
use App\Domains\Engagement\Models\EngagementSurvey;
use App\Domains\Engagement\Models\EngagementSurveyQuestion;
use App\Domains\Engagement\Services\EngagementAnalyticsService;
use App\Domains\Organization\Models\BusinessUnit;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Department;
use App\Domains\Platform\Contracts\TenantContext;
use App\Domains\Shared\Models\Permission;
use App\Domains\Shared\Models\PermissionGroup;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Database\Seeders\EngagementPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EngagementConfidentialityAndRBACTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(EngagementPermissionSeeder::class);
    }

    public function test_multi_tenant_isolation_and_manager_threshold_suppression(): void
    {
        // Tenant A
        $tenantA = Tenant::factory()->create();
        $companyA = Company::factory()->create(['tenant_id' => $tenantA->id]);

        $buA = BusinessUnit::query()->create([
            'tenant_id' => $tenantA->id,
            'company_id' => $companyA->id,
            'code' => 'BU-ENG',
            'name' => 'Engineering BU',
        ]);

        $deptA = Department::query()->create([
            'tenant_id' => $tenantA->id,
            'business_unit_id' => $buA->id,
            'department_code' => 'DEPT-ENG',
            'department_name' => 'Core Engineering',
        ]);

        $mgrUserA = User::factory()->create(['tenant_id' => $tenantA->id]);
        $this->grant($mgrUserA, ['hcm.engagement.view', 'hcm.engagement.results.view']);

        $mgrA = Employee::query()->create([
            'tenant_id' => $tenantA->id,
            'user_id' => $mgrUserA->id,
            'company_id' => $companyA->id,
            'department_id' => $deptA->id,
            'employee_number' => 'MGR-A-01',
            'employee_code' => 'MGR-A-01',
            'first_name' => 'Manager',
            'last_name' => 'One',
            'joining_date' => now()->toDateString(),
        ]);

        $surveyA = EngagementSurvey::query()->create([
            'tenant_id' => $tenantA->id,
            'code' => 'SURV-TENANT-A',
            'title' => 'Tenant A Survey',
            'survey_type' => 'engagement',
            'confidentiality_type' => 'anonymous',
        ]);

        $campA = EngagementCampaign::query()->create([
            'tenant_id' => $tenantA->id,
            'survey_id' => $surveyA->id,
            'code' => 'CAMP-A-01',
            'name' => 'Campaign A',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(7)->toDateString(),
            'status' => 'active',
            'minimum_response_threshold' => 5,
        ]);

        // Tenant B
        $tenantB = Tenant::factory()->create();
        $companyB = Company::factory()->create(['tenant_id' => $tenantB->id]);
        $userB = User::factory()->create(['tenant_id' => $tenantB->id]);
        $this->grant($userB, ['hcm.engagement.view', 'hcm.engagement.results.view']);

        // 1. Multi-Tenant isolation: User B from Tenant B cannot access Tenant A survey results
        $respB = $this->actingAs($userB)->getJson("/api/v1/hcm/engagement/campaigns/{$campA->id}/results");
        $respB->assertStatus(404);

        // 2. Manager Suppression Check: When department responses < 5, manager results endpoint returns suppressed payload

        // Add 2 responses (less than threshold 5)
        for ($i = 0; $i < 2; $i++) {
            EngagementResponse::query()->create([
                'tenant_id' => $tenantA->id,
                'survey_id' => $surveyA->id,
                'campaign_id' => $campA->id,
                'department_id' => $deptA->id,
                'confidentiality_type' => 'anonymous',
                'response_status' => 'submitted',
                'started_at' => now(),
                'submitted_at' => now(),
                'is_locked' => true,
            ]);
        }

        $mgrResults = $this->actingAs($mgrUserA)->getJson("/api/v1/hcm/manager/engagement/results/{$campA->id}");
        $mgrResults->assertStatus(200);
        $mgrResults->assertJson([
            'suppressed' => true,
        ]);

        // 3. Analytics Service Ingestion Check
        $analyticsService = app(EngagementAnalyticsService::class);
        $metrics = $analyticsService->generateEngagementMetrics($tenantA->id);

        $this->assertArrayHasKey('engagement_score', $metrics);
        $this->assertArrayHasKey('enps', $metrics);
        $this->assertArrayHasKey('survey_response_rate', $metrics);
    }

    private function grant(User $user, array $permissions): void
    {
        $group = PermissionGroup::query()->firstOrCreate(['name' => 'engagement'], ['label' => 'Engagement']);
        foreach ($permissions as $p) {
            $perm = Permission::query()->firstOrCreate(['name' => $p], ['permission_group_id' => $group->id, 'label' => $p]);
            $user->permissions()->syncWithoutDetaching([$perm->id]);
        }
    }
}
