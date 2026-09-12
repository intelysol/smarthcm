<?php

namespace App\Domains\WorkforceGovernance\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HcmGovLineageNode extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'hcm_gov_lineage_nodes';

    protected $fillable = [
        'tenant_id',
        'node_code',
        'name',
        'node_type',
        'domain',
        'system_name',
        'schema_definition',
    ];

    protected $casts = [
        'schema_definition' => 'array',
    ];

    public function downstreamEdges()
    {
        return $this->hasMany(HcmGovLineageEdge::class, 'source_node_id');
    }

    public function upstreamEdges()
    {
        return $this->hasMany(HcmGovLineageEdge::class, 'target_node_id');
    }
}
