<?php

namespace Tests\Feature\OrganizationDesign;

use App\Domains\Organization\Models\BusinessUnit;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Department;
use App\Domains\Organization\Models\Team;
use App\Domains\OrganizationDesign\Services\OrganizationScenarioService;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationScenarioAndComparisonTest extends TestCase
{
    use RefreshDatabase;

    public function test_organization_scenario_cloning_and_structure_comparison(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $company = Company::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Acme Global Holding']);
        $bu = BusinessUnit::create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'name' => 'EMEA BU', 'code' => 'BU-EMEA']);
        $dept = Department::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'business_unit_id' => $bu->id,
            'department_code' => 'DEPT-ENG',
            'department_name' => 'Product Engineering',
        ]);
        $section = \App\Domains\Organization\Models\Section::create([
            'tenant_id' => $tenant->id,
            'department_id' => $dept->id,
            'section_code' => 'SEC-BACKEND',
            'section_name' => 'Backend Engineering Section',
        ]);
        $team = Team::create([
            'tenant_id' => $tenant->id,
            'section_id' => $section->id,
            'team_name' => 'Platform Core Team',
            'code' => 'TEAM-CORE',
        ]);

        $scenarioService = app(OrganizationScenarioService::class);

        // 1. Create a planned scenario (e.g. FY27 Org Restructure)
        $scenario = $scenarioService->createScenario($tenant->id, [
            'code' => 'SCENARIO-FY27',
            'name' => 'FY2027 Reorganization Plan',
            'description' => 'Restructuring engineering into separate Cloud and AI divisions',
        ], $user);

        $this->assertEquals('draft', $scenario->status);

        // 2. Non-destructively clone live Core HR structure into scenario
        $clonedCount = $scenarioService->cloneLiveStructureToScenario($scenario);
        $this->assertEquals(4, $clonedCount); // Company + Department + Section + Team

        // 3. Add a planned new Department node in the scenario
        $compNode = $scenario->nodes()->where('node_type', 'company')->first();
        $newNode = $scenarioService->addScenarioNode($scenario, [
            'node_type' => 'department',
            'parent_scenario_node_id' => $compNode->id,
            'code' => 'DEPT-AI',
            'name' => 'Artificial Intelligence & Data Ops',
            'metadata_payload' => ['target_headcount' => 15],
        ]);

        $this->assertEquals('add', $newNode->action_type);

        // 4. Compare scenario vs live
        $diff = $scenarioService->compareScenarioWithLive($scenario);

        $this->assertEquals(5, $diff['total_scenario_nodes']);
        $this->assertEquals(1, $diff['added_nodes_count']);
        $this->assertEquals('DEPT-AI', $diff['added_nodes'][0]->code);

        // 5. Verify live Core HR remained completely unchanged
        $this->assertCount(1, Department::where('tenant_id', $tenant->id)->get());
    }
}
