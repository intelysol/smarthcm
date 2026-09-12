<?php

declare(strict_types=1);

namespace App\Domains\Performance\Models;

use App\Domains\Employee\Models\Employee;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PerformanceDevelopmentPlan extends PerformanceModel
{
    protected $fillable = [
        'tenant_id',
        'employee_id',
        'cycle_id',
        'status',
        'summary',
        'details',
        'start_date',
        'end_date',
    ];

    protected function casts(): array
    {
        return [
            'details' => 'array',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function cycle(): BelongsTo
    {
        return $this->belongsTo(PerformanceCycle::class, 'cycle_id');
    }

    public function actions(): HasMany
    {
        return $this->hasMany(PerformanceDevelopmentAction::class, 'plan_id');
    }
}
