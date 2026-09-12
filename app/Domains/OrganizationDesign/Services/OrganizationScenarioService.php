<?php

namespace App\Domains\OrganizationDesign\Services;

use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Department;
use App\Domains\Organization\Models\Team;
use App\Domains\OrganizationDesign\Models\OrgDesignScenario;
use App\Domains\OrganizationDesign\Models\OrgDesignScenarioNode;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OrganizationScenarioService
{
    /**
     * Create a new organization design scenario.
     */
    public function createScenario(string $tenantId, array $data, ?User $actor = null): OrgDesignScenario
    {
        return OrgDesignScenario::create([
            'tenant_id' => $tenantId,
            'code' => strtoupper($data['code']),
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? 'draft',
            'effective_target_date' => $data['effective_target_date'] ?? null,
            'created_by_user_id' => $actor?->id,
        ]);
    }

    /**
     * Clone live Core HR organization structure into scenario nodes for non-destructive modeling.
     */
    public function cloneLiveStructureToScenario(OrgDesignScenario $scenario): int
    {
        $tenantId = $scenario->tenant_id;
        $clonedCount = 0;

        return DB::transaction(function () use ($scenario, $tenantId, &$clonedCount) {
            $companies = Company::where('tenant_id', $tenantId)->get();

            foreach ($companies as $company) {
                $compNode = OrgDesignScenarioNode::create([
                    'tenant_id' => $tenantId,
                    'scenario_id' => $scenario->id,
                    'node_type' => 'company',
                    'source_node_id' => $company->id,
                    'parent_scenario_node_id' => null,
                    'code' => $company->code ?? ('COMP-' . substr($company->id, 0, 6)),
                    'name' => $company->name,
                    'action_type' => 'existing',
                ]);
                $clonedCount++;

                $businessUnits = \App\Domains\Organization\Models\BusinessUnit::where('tenant_id', $tenantId)
                    ->where('company_id', $company->id)
                    ->get();

                foreach ($businessUnits as $bu) {
                    $departments = Department::where('tenant_id', $tenantId)
                        ->where('business_unit_id', $bu->id)
                        ->get();

                foreach ($departments as $department) {
                    $deptNode = OrgDesignScenarioNode::create([
                        'tenant_id' => $tenantId,
                        'scenario_id' => $scenario->id,
                        'node_type' => 'department',
                        'source_node_id' => $department->id,
                        'parent_scenario_node_id' => $compNode->id,
                        'code' => $department->department_code ?? ('DEPT-' . substr($department->id, 0, 6)),
                        'name' => $department->department_name,
                        'action_type' => 'existing',
                    ]);
                    $clonedCount++;

                    $sections = \App\Domains\Organization\Models\Section::where('tenant_id', $tenantId)
                        ->where('department_id', $department->id)
                        ->get();

                    foreach ($sections as $section) {
                        $secNode = OrgDesignScenarioNode::create([
                            'tenant_id' => $tenantId,
                            'scenario_id' => $scenario->id,
                            'node_type' => 'section',
                            'source_node_id' => $section->id,
                            'parent_scenario_node_id' => $deptNode->id,
                            'code' => $section->section_code ?? ('SEC-' . substr($section->id, 0, 6)),
                            'name' => $section->section_name,
                            'action_type' => 'existing',
                        ]);
                        $clonedCount++;

                        $teams = Team::where('tenant_id', $tenantId)
                            ->where('section_id', $section->id)
                            ->get();

                        foreach ($teams as $team) {
                            OrgDesignScenarioNode::create([
                                'tenant_id' => $tenantId,
                                'scenario_id' => $scenario->id,
                                'node_type' => 'team',
                                'source_node_id' => $team->id,
                                'parent_scenario_node_id' => $secNode->id,
                                'code' => 'TEAM-' . substr($team->id, 0, 6),
                                'name' => $team->team_name,
                                'action_type' => 'existing',
                            ]);
                            $clonedCount++;
                        }
                    }
                }
            }
            }

            return $clonedCount;
        });
    }

    /**
     * Add a new planned node to scenario.
     */
    public function addScenarioNode(OrgDesignScenario $scenario, array $data): OrgDesignScenarioNode
    {
        return OrgDesignScenarioNode::create([
            'tenant_id' => $scenario->tenant_id,
            'scenario_id' => $scenario->id,
            'node_type' => $data['node_type'],
            'source_node_id' => null,
            'parent_scenario_node_id' => $data['parent_scenario_node_id'] ?? null,
            'code' => strtoupper($data['code']),
            'name' => $data['name'],
            'action_type' => 'add',
            'metadata_payload' => $data['metadata_payload'] ?? null,
        ]);
    }

    /**
     * Compare Scenario against current live state.
     */
    public function compareScenarioWithLive(OrgDesignScenario $scenario): array
    {
        $nodes = $scenario->nodes;

        $addedNodes = $nodes->where('action_type', 'add')->values();
        $modifiedNodes = $nodes->where('action_type', 'modify')->values();
        $mergedNodes = $nodes->where('action_type', 'merge')->values();
        $removedNodes = $nodes->where('action_type', 'remove')->values();
        $existingNodes = $nodes->where('action_type', 'existing')->values();

        return [
            'scenario_id' => $scenario->id,
            'scenario_name' => $scenario->name,
            'total_scenario_nodes' => $nodes->count(),
            'added_nodes_count' => $addedNodes->count(),
            'modified_nodes_count' => $modifiedNodes->count(),
            'merged_nodes_count' => $mergedNodes->count(),
            'removed_nodes_count' => $removedNodes->count(),
            'added_nodes' => $addedNodes,
            'modified_nodes' => $modifiedNodes,
            'merged_nodes' => $mergedNodes,
            'removed_nodes' => $removedNodes,
        ];
    }
}
