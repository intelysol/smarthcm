<?php

namespace App\Domains\WorkforceIntelligence\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommandCenterAudit extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'hcm_command_center_audits';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'action_type',
        'entity_type',
        'entity_id',
        'summary',
        'details',
        'ip_address',
        'user_agent',
        'performed_at',
    ];

    protected $casts = [
        'details' => 'array',
        'performed_at' => 'datetime',
    ];
}
