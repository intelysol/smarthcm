<?php

namespace App\Domains\Career\Models;

use App\Domains\Employee\Models\Employee;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'tenant_id', 'pool_id', 'employee_id', 'added_by', 'added_at', 'reason', 'status'
])]
class TalentPoolMember extends CareerModel
{
    protected function casts(): array
    {
        return [
            'added_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<TalentPool, TalentPoolMember> */
    public function pool(): BelongsTo
    {
        return $this->belongsTo(TalentPool::class, 'pool_id');
    }

    /** @return BelongsTo<Employee, TalentPoolMember> */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /** @return BelongsTo<Employee, TalentPoolMember> */
    public function adder(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'added_by');
    }
}
