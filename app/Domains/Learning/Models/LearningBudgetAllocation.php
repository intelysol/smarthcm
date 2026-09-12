<?php

namespace App\Domains\Learning\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['tenant_id', 'budget_id', 'allocated_to_type', 'allocated_to_id', 'amount', 'spent_amount'])]
class LearningBudgetAllocation extends LearningModel
{
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'spent_amount' => 'decimal:2',
        ];
    }

    /** @return BelongsTo<LearningBudget, LearningBudgetAllocation> */
    public function budget(): BelongsTo
    {
        return $this->belongsTo(LearningBudget::class, 'budget_id');
    }
}
