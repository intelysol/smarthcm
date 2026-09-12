<?php

namespace App\Domains\WorkforceIntelligence\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommandCenterSavedView extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'hcm_command_center_saved_views';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'name',
        'persona',
        'filter_criteria',
        'is_default',
    ];

    protected $casts = [
        'filter_criteria' => 'array',
        'is_default' => 'boolean',
    ];
}
