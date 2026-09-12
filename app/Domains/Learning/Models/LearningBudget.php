<?php

namespace App\Domains\Learning\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['tenant_id', 'title', 'fiscal_year', 'scope_type', 'scope_id', 'total_allocated', 'total_spent', 'currency', 'status'])]
class LearningBudget extends LearningModel
{
    protected function casts(): array
    {
        return [
            'total_allocated' => 'decimal:2',
            'total_spent' => 'decimal:2',
        ];
    }

    /** @return HasMany<LearningBudgetAllocation> */
    public function allocations(): HasMany
    {
        return $this->hasMany(LearningBudgetAllocation::class, 'budget_id');
    }
}
