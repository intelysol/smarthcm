<?php

namespace App\Domains\Recruitment\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HcmRecruitmentApplicationStage extends Model
{
    use HasUuids;

    protected $table = 'hcm_recruitment_application_stages';

    protected $fillable = [
        'tenant_id',
        'name',
        'code',
        'stage_order',
        'is_system_stage',
    ];

    protected $casts = [
        'stage_order' => 'integer',
        'is_system_stage' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(HcmRecruitmentApplication::class, 'stage_id');
    }
}
