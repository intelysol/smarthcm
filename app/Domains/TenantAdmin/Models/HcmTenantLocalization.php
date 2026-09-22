<?php

namespace App\Domains\TenantAdmin\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HcmTenantLocalization extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'hcm_tenant_localizations';

    protected $fillable = [
        'tenant_id',
        'country_code',
        'language',
        'timezone',
        'currency',
        'currency_symbol',
        'date_format',
        'time_format',
        'week_start',
        'fiscal_year_start_month',
        'tax_identifier_type',
        'national_id_mask',
        'is_pakistan_statutory_enabled',
    ];

    protected $casts = [
        'fiscal_year_start_month' => 'integer',
        'is_pakistan_statutory_enabled' => 'boolean',
    ];
}
