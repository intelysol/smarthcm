<?php

namespace App\Domains\Career\Models;

use App\Domains\Employee\Models\Employee;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable([
    'tenant_id', 'uuid', 'employee_id', 'gap_type', 'gap_id', 'recommendation_type',
    'recommended_item_id', 'title', 'description', 'priority', 'status'
])]
class CareerDevelopmentRecommendation extends CareerModel
{
    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            $model->uuid ??= (string) Str::uuid();
        });
    }

    /** @return BelongsTo<Employee, CareerDevelopmentRecommendation> */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
