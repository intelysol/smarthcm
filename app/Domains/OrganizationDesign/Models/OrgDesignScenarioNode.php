<?php

namespace App\Domains\OrganizationDesign\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrgDesignScenarioNode extends Model
{
    use HasUuids;

    protected $table = 'org_design_scenario_nodes';

    protected $fillable = [
        'tenant_id',
        'scenario_id',
        'node_type',
        'source_node_id',
        'parent_scenario_node_id',
        'code',
        'name',
        'action_type',
        'metadata_payload',
    ];

    protected $casts = [
        'metadata_payload' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function scenario(): BelongsTo
    {
        return $this->belongsTo(OrgDesignScenario::class, 'scenario_id');
    }

    public function parentNode(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_scenario_node_id');
    }

    public function childNodes(): HasMany
    {
        return $this->hasMany(self::class, 'parent_scenario_node_id');
    }
}
