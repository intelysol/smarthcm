<?php

namespace App\Domains\WorkforceIntelligence\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommandCenterDashboard extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'hcm_command_center_dashboards';

    protected $fillable = [
        'tenant_id',
        'dashboard_code',
        'name',
        'persona',
        'description',
        'is_system_default',
        'layout_config',
    ];

    protected $casts = [
        'is_system_default' => 'boolean',
        'layout_config' => 'array',
    ];

    public function widgets()
    {
        return $this->hasMany(CommandCenterWidget::class, 'dashboard_id');
    }
}
