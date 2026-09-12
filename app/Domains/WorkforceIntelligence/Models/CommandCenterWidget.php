<?php

namespace App\Domains\WorkforceIntelligence\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommandCenterWidget extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'hcm_command_center_widgets';

    protected $fillable = [
        'tenant_id',
        'dashboard_id',
        'widget_code',
        'title',
        'widget_type',
        'grid_x',
        'grid_y',
        'grid_width',
        'grid_height',
        'configuration',
        'is_visible',
    ];

    protected $casts = [
        'grid_x' => 'integer',
        'grid_y' => 'integer',
        'grid_width' => 'integer',
        'grid_height' => 'integer',
        'configuration' => 'array',
        'is_visible' => 'boolean',
    ];

    public function dashboard()
    {
        return $this->belongsTo(CommandCenterDashboard::class, 'dashboard_id');
    }
}
