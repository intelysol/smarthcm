<?php

namespace App\Domains\TenantAdmin\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HcmTenantConfigVersion extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'hcm_tenant_config_versions';

    protected $fillable = [
        'tenant_id',
        'config_id',
        'config_key',
        'version_number',
        'old_value',
        'new_value',
        'change_reason',
        'changed_by_user_id',
        'published_at',
    ];

    protected $casts = [
        'version_number' => 'integer',
        'old_value' => 'array',
        'new_value' => 'array',
        'published_at' => 'datetime',
    ];

    public function configuration()
    {
        return $this->belongsTo(HcmTenantConfiguration::class, 'config_id');
    }
}
