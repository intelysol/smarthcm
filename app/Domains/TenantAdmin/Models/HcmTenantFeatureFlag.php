<?php

namespace App\Domains\TenantAdmin\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HcmTenantFeatureFlag extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'hcm_tenant_feature_flags';

    protected $fillable = [
        'tenant_id',
        'feature_key',
        'name',
        'description',
        'is_enabled',
        'dependencies',
        'rollout_scope',
        'rollout_value',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'dependencies' => 'array',
    ];
}
