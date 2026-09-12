<?php

namespace App\Domains\Analytics\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmAnalyticsDimension extends Model
{
    use HasUuids;

    protected $table = 'hcm_analytics_dimensions';

    protected $fillable = [
        'tenant_id',
        'dimension_type',
        'dimension_key',
        'name',
        'attributes',
        'valid_from',
        'valid_to',
        'is_active',
    ];

    protected $casts = [
        'attributes' => 'array',
        'valid_from' => 'date',
        'valid_to' => 'date',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
