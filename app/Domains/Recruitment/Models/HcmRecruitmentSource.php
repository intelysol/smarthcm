<?php

namespace App\Domains\Recruitment\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HcmRecruitmentSource extends Model
{
    use HasUuids;

    protected $table = 'hcm_recruitment_sources';

    protected $fillable = [
        'tenant_id',
        'name',
        'code',
        'type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function candidates(): HasMany
    {
        return $this->hasMany(HcmRecruitmentCandidate::class, 'source_id');
    }
}
