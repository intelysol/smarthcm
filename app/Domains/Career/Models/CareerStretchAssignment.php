<?php

namespace App\Domains\Career\Models;

use App\Domains\Employee\Models\Employee;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable([
    'tenant_id', 'uuid', 'employee_id', 'title', 'description',
    'leader_employee_id', 'start_date', 'end_date', 'skills_targeted',
    'status', 'version'
])]
class CareerStretchAssignment extends CareerModel
{
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'skills_targeted' => 'array',
            'version' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            $model->uuid ??= (string) Str::uuid();
        });
    }

    /** @return BelongsTo<Employee, CareerStretchAssignment> */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /** @return BelongsTo<Employee, CareerStretchAssignment> */
    public function leader(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'leader_employee_id');
    }
}
