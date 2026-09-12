<?php

namespace App\Domains\Engagement\Models;

use App\Domains\Employee\Models\Employee;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EngagementGoal extends EngagementModel
{
    protected $table = 'engagement_goals';

    protected $fillable = [
        'tenant_id',
        'title',
        'metric_type',
        'baseline_value',
        'target_value',
        'current_value',
        'deadline',
        'owner_id',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'baseline_value' => 'decimal:4',
            'target_value' => 'decimal:4',
            'current_value' => 'decimal:4',
            'deadline' => 'date',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'owner_id');
    }
}
