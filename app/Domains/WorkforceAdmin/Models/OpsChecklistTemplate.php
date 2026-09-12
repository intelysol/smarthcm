<?php

namespace App\Domains\WorkforceAdmin\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OpsChecklistTemplate extends Model
{
    use HasUuids;

    protected $table = 'hcm_ops_checklist_templates';

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'trigger_type',
        'default_items',
        'is_active',
    ];

    protected $casts = [
        'default_items' => 'array',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function instances(): HasMany
    {
        return $this->hasMany(OpsChecklistInstance::class, 'template_id');
    }
}
