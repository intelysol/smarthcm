<?php

namespace Tests\Feature\EmployeeRelations;

use App\Domains\EmployeeRelations\Models\EmployeeRelationCase;
use App\Domains\EmployeeRelations\Models\EmployeeRelationCaseType;
use App\Domains\EmployeeRelations\Services\EmployeeRelationAnalyticsService;
use App\Domains\EmployeeRelations\Services\EmployeeRelationCaseService;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Permission;
use App\Domains\Shared\Models\PermissionGroup;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Database\Seeders\EmployeeRelationsCaseTypeSeeder;
use Database\Seeders\EmployeeRelationsPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeRelationsAnalyticsAndExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(EmployeeRelationsPermissionSeeder::class);
        $this->seed(EmployeeRelationsCaseTypeSeeder::class);
    }

    public function test_analytics_metrics_ai_safety_and_case_export(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $hrUser = User::factory()->create(['tenant_id' => $tenant->id]);
        $this->grant($hrUser, [
            'hcm.employee_relations.manage',
            'hcm.employee_relations.case.view',
            'hcm.employee_relations.report.view',
            'hcm.employee_relations.report.export',
        ]);

        $caseService = app(EmployeeRelationCaseService::class);
        $analyticsService = app(EmployeeRelationAnalyticsService::class);

        $caseType = EmployeeRelationCaseType::query()->where('code', 'GRIEVANCE')->first();
        $case = $caseService->createCase($tenant->id, [
            'case_type_id' => $caseType->id,
            'title' => 'Analytics Test Case',
            'summary' => 'Case for verifying analytics and export functions.',
        ], $hrUser);

        // 1. Analytics calculation
        $metrics = $analyticsService->generateMetrics($tenant->id);
        $this->assertEquals(1, $metrics['total_cases']);
        $this->assertEquals(1, $metrics['open_cases']);
        $this->assertArrayHasKey('sla_compliance_rate', $metrics);
        $this->assertArrayHasKey('by_type', $metrics);

        // 2. Case Export Endpoint
        $exportResp = $this->actingAs($hrUser)->postJson("/api/v1/hcm/employee-relations/cases/{$case->id}/export");
        $exportResp->assertStatus(200);
        $exportResp->assertJsonStructure([
            'bundle_type',
            'exported_at',
            'case',
            'allegations',
            'decisions',
            'corrective_actions',
            'appeals',
        ]);

        // 3. AI Summarization Endpoint with Safety Verification
        $aiResp = $this->actingAs($hrUser)->postJson("/api/v1/hcm/employee-relations/cases/{$case->id}/ai/summary");
        $aiResp->assertStatus(200);
        $aiResp->assertJsonStructure([
            'case_number',
            'generated_at',
            'summary',
            'safety_notice',
        ]);
        $this->assertStringContainsString('timeline and synthesis tool only', $aiResp->json('safety_notice'));
    }

    private function grant(User $user, array $permissions): void
    {
        $group = PermissionGroup::query()->firstOrCreate(['name' => 'er_test'], ['label' => 'ER Test']);
        foreach ($permissions as $p) {
            $perm = Permission::query()->firstOrCreate(['name' => $p], ['permission_group_id' => $group->id, 'label' => $p]);
            $user->permissions()->syncWithoutDetaching([$perm->id]);
        }
    }
}
