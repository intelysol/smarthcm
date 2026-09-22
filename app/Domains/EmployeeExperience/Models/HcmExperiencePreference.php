<?php

namespace App\Domains\EmployeeExperience\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmExperiencePreference extends Model
{
    use HasUuids;

    protected $table = 'hcm_experience_preferences';

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'widget_preferences',
        'quick_action_order',
        'notification_channels',
        'theme_preference',
    ];

    protected $casts = [
        'widget_preferences' => 'array',
        'quick_action_order' => 'array',
        'notification_channels' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
