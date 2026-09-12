<?php

namespace App\Domains\OrganizationDesign\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrgDesignHealthIssue extends Model
{
    use HasUuids;

    protected $table = 'org_design_health_issues';

    protected $fillable = [
        'tenant_id',
        'issue_type',
        'severity',
        'entity_type',
        'entity_id',
        'title',
        'details',
        'status',
    ];

    protected $casts = [
        'details' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
