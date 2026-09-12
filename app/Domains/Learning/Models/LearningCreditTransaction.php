<?php

namespace App\Domains\Learning\Models;

use App\Domains\Employee\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['tenant_id', 'employee_id', 'source_type', 'source_id', 'credits', 'description', 'awarded_at', 'awarded_by'])]
class LearningCreditTransaction extends LearningModel
{
    protected function casts(): array
    {
        return [
            'credits' => 'decimal:2',
            'awarded_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Employee, LearningCreditTransaction> */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /** @return BelongsTo<User, LearningCreditTransaction> */
    public function awarder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'awarded_by');
    }
}
