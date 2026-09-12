<?php

namespace App\Domains\WorkforceGovernance\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HcmGovAudit extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'hcm_gov_audits';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'action',
        'target_entity_type',
        'target_entity_id',
        'summary',
        'details',
        'performed_at',
    ];

    protected $casts = [
        'details' => 'array',
        'performed_at' => 'datetime',
    ];
}
