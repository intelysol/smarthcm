<?php

namespace App\Domains\TenantAdmin\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HcmTenantBranding extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'hcm_tenant_brandings';

    protected $fillable = [
        'tenant_id',
        'company_name',
        'logo_url',
        'favicon_url',
        'primary_color',
        'secondary_color',
        'accent_color',
        'custom_css',
        'login_banner_text',
        'portal_title',
    ];
}
