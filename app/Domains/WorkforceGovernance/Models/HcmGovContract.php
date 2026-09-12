<?php

namespace App\Domains\WorkforceGovernance\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HcmGovContract extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'hcm_gov_contracts';

    protected $fillable = [
        'tenant_id',
        'contract_code',
        'name',
        'producer_module',
        'consumer_modules',
        'version',
        'schema_contract',
        'status',
        'last_validated_at',
        'breaking_change_notes',
    ];

    protected $casts = [
        'consumer_modules' => 'array',
        'schema_contract' => 'array',
        'version' => 'integer',
        'last_validated_at' => 'datetime',
    ];
}
