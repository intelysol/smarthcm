<?php

namespace App\Domains\Learning\Models;

use App\Domains\Employee\Models\Employee;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['tenant_id', 'employee_id', 'total_earned', 'total_required', 'period_start', 'period_end'])]
class LearningCredit extends LearningModel
{
    protected function casts(): array
    {
        return [
            'total_earned' => 'decimal:2',
            'total_required' => 'decimal:2',
            'period_start' => 'date',
            'period_end' => 'date',
        ];
    }

    /** @return BelongsTo<Employee, LearningCredit> */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /** @return HasMany<LearningCreditTransaction> */
    public function transactions(): HasMany
    {
        return $this->hasMany(LearningCreditTransaction::class, 'employee_id', 'employee_id');
    }
}
