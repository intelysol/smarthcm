<?php

namespace App\Domains\WorkforceIntelligence\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommandCenterUserPreference extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'hcm_command_center_user_preferences';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'default_persona',
        'default_period_type',
        'pinned_widget_ids',
        'custom_dashboard_layout',
    ];

    protected $casts = [
        'pinned_widget_ids' => 'array',
        'custom_dashboard_layout' => 'array',
    ];
}
