<?php

namespace App\Domains\Analytics\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmAnalyticsDataProduct extends Model
{
    use HasUuids;

    protected $table = 'hcm_analytics_data_products';

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'domain_module',
        'description',
        'schema_fields',
        'supported_dimensions',
        'is_active',
    ];

    protected $casts = [
        'schema_fields' => 'array',
        'supported_dimensions' => 'array',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
