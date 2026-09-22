<?php

namespace App\Domains\TenantAdmin\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HcmTenantConfiguration extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'hcm_tenant_configurations';

    protected $fillable = [
        'tenant_id',
        'scope',
        'scope_id',
        'category',
        'config_key',
        'config_value',
        'value_type',
        'is_overridable',
        'version_number',
        'status',
        'effective_date',
        'created_by_user_id',
        'published_by_user_id',
    ];

    protected $casts = [
        'config_value' => 'array',
        'is_overridable' => 'boolean',
        'version_number' => 'integer',
        'effective_date' => 'date',
    ];

    public function versions()
    {
        return $this->hasMany(HcmTenantConfigVersion::class, 'config_id');
    }
}
