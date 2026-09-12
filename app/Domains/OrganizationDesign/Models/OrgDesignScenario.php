<?php

namespace App\Domains\OrganizationDesign\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrgDesignScenario extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'org_design_scenarios';

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'description',
        'status',
        'effective_target_date',
        'created_by_user_id',
    ];

    protected $casts = [
        'effective_target_date' => 'date',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function nodes(): HasMany
    {
        return $this->hasMany(OrgDesignScenarioNode::class, 'scenario_id');
    }
}
