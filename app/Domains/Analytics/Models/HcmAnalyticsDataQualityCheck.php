<?php

namespace App\Domains\Analytics\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HcmAnalyticsDataQualityCheck extends Model
{
    use HasUuids;

    protected $table = 'hcm_analytics_data_quality_checks';

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'domain_module',
        'description',
        'severity',
        'check_type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(HcmAnalyticsDataQualityResult::class, 'check_id');
    }
}
