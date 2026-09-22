<?php

namespace App\Domains\TenantAdmin\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HcmTenantRoleTemplate extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'hcm_tenant_role_templates';

    protected $fillable = [
        'tenant_id',
        'template_code',
        'name',
        'description',
        'assigned_permissions',
        'is_system_template',
    ];

    protected $casts = [
        'assigned_permissions' => 'array',
        'is_system_template' => 'boolean',
    ];
}
