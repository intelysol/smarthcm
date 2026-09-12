<?php

namespace App\Domains\Analytics\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class HcmAnalyticsDashboardDefinition extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'hcm_analytics_dashboard_definitions';

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'dashboard_type',
        'description',
        'layout_config',
        'allowed_roles',
        'is_system',
        'created_by',
    ];

    protected $casts = [
        'layout_config' => 'array',
        'allowed_roles' => 'array',
        'is_system' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function widgets(): HasMany
    {
        return $this->hasMany(HcmAnalyticsDashboardWidget::class, 'dashboard_id');
    }
}
