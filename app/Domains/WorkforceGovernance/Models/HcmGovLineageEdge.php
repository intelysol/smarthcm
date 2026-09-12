<?php

namespace App\Domains\WorkforceGovernance\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HcmGovLineageEdge extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'hcm_gov_lineage_edges';

    protected $fillable = [
        'tenant_id',
        'source_node_id',
        'target_node_id',
        'relationship_type',
        'transformation_logic',
    ];

    public function sourceNode()
    {
        return $this->belongsTo(HcmGovLineageNode::class, 'source_node_id');
    }

    public function targetNode()
    {
        return $this->belongsTo(HcmGovLineageNode::class, 'target_node_id');
    }
}
