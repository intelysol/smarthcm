<?php

namespace App\Domains\Engagement\Models;

use App\Domains\Employee\Models\Employee;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EngagementActionItem extends EngagementModel
{
    protected $table = 'engagement_action_items';

    protected $fillable = [
        'tenant_id',
        'action_plan_id',
        'title',
        'description',
        'owner_id',
        'due_date',
        'status',
        'completion_percentage',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'completion_percentage' => 'decimal:2',
        ];
    }

    public function actionPlan(): BelongsTo
    {
        return $this->belongsTo(EngagementActionPlan::class, 'action_plan_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'owner_id');
    }
}
