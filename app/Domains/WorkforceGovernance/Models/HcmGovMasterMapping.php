<?php

namespace App\Domains\WorkforceGovernance\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HcmGovMasterMapping extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'hcm_gov_master_mappings';

    protected $fillable = [
        'tenant_id',
        'entity_type',
        'source_system',
        'source_id',
        'source_code',
        'target_system',
        'target_id',
        'target_code',
        'mapping_status',
        'effective_from',
        'effective_to',
        'verified_by_user_id',
    ];

    protected $casts = [
        'effective_from' => 'date',
        'effective_to' => 'date',
    ];
}
