<?php

namespace App\Domains\ResponsibleAi\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HcmAiGovKillSwitch extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'hcm_ai_gov_kill_switches';

    protected $fillable = [
        'tenant_id',
        'scope',
        'target_identifier',
        'is_active',
        'activation_reason',
        'activated_by_user_id',
        'activated_at',
        'deactivated_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'activated_at' => 'datetime',
        'deactivated_at' => 'datetime',
    ];
}
