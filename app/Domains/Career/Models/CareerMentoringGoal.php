<?php

namespace App\Domains\Career\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'tenant_id', 'relationship_id', 'title', 'description', 'target_date', 'status'
])]
class CareerMentoringGoal extends CareerModel
{
    protected function casts(): array
    {
        return [
            'target_date' => 'date',
        ];
    }

    /** @return BelongsTo<CareerMentoringRelationship, CareerMentoringGoal> */
    public function relationship(): BelongsTo
    {
        return $this->belongsTo(CareerMentoringRelationship::class, 'relationship_id');
    }
}
