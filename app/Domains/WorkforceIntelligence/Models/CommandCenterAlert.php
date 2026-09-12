<?php

namespace App\Domains\WorkforceIntelligence\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommandCenterAlert extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'hcm_command_center_alerts';

    protected $fillable = [
        'tenant_id',
        'department_id',
        'alert_code',
        'severity',
        'category',
        'title',
        'message',
        'source_module',
        'action_url',
        'status',
        'acknowledged_by_user_id',
        'acknowledged_at',
        'expires_at',
    ];

    protected $casts = [
        'acknowledged_at' => 'datetime',
        'expires_at' => 'datetime',
    ];
}
