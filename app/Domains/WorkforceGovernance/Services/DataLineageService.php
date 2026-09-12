<?php

namespace App\Domains\WorkforceGovernance\Services;

use App\Domains\WorkforceGovernance\Models\HcmGovLineageEdge;
use App\Domains\WorkforceGovernance\Models\HcmGovLineageNode;
use Illuminate\Support\Str;

class DataLineageService
{
    public function recordNode(array $data): HcmGovLineageNode
    {
        return HcmGovLineageNode::firstOrCreate(
            [
                'tenant_id' => $data['tenant_id'],
                'node_code' => $data['node_code'],
            ],
            [
                'name' => $data['name'],
                'node_type' => $data['node_type'],
                'domain' => $data['domain'],
                'system_name' => $data['system_name'] ?? 'CORE_HCM',
                'schema_definition' => $data['schema_definition'] ?? [],
            ]
        );
    }

    public function recordEdge(string $tenantId, string $sourceCode, string $targetCode, string $relType, ?string $logic = null): HcmGovLineageEdge
    {
        $source = HcmGovLineageNode::where('tenant_id', $tenantId)->where('node_code', $sourceCode)->firstOrFail();
        $target = HcmGovLineageNode::where('tenant_id', $tenantId)->where('node_code', $targetCode)->firstOrFail();

        return HcmGovLineageEdge::firstOrCreate(
            [
                'tenant_id' => $tenantId,
                'source_node_id' => $source->id,
                'target_node_id' => $target->id,
            ],
            [
                'relationship_type' => $relType,
                'transformation_logic' => $logic,
            ]
        );
    }

    public function getLineage(string $tenantId, string $nodeCode): array
    {
        $node = HcmGovLineageNode::where('tenant_id', $tenantId)->where('node_code', $nodeCode)->first();
        if (!$node) {
            return [];
        }

        $upstream = HcmGovLineageEdge::with('sourceNode')
            ->where('tenant_id', $tenantId)
            ->where('target_node_id', $node->id)
            ->get();

        $downstream = HcmGovLineageEdge::with('targetNode')
            ->where('tenant_id', $tenantId)
            ->where('source_node_id', $node->id)
            ->get();

        return [
            'current_node' => $node->toArray(),
            'upstream' => $upstream->toArray(),
            'downstream' => $downstream->toArray(),
        ];
    }
}
