<?php

namespace App\Domains\Career\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'tenant_id', 'plan_id', 'action_type', 'title', 'description',
    'start_date', 'due_date', 'status', 'completion_percentage', 'completed_at'
])]
class CareerPlanAction extends CareerModel
{
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'due_date' => 'date',
            'completion_percentage' => 'decimal:2',
            'completed_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<CareerPlan, CareerPlanAction> */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(CareerPlan::class, 'plan_id');
    }
}
