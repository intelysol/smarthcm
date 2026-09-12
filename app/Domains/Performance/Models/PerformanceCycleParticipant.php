<?php

declare(strict_types=1);

namespace App\Domains\Performance\Models;

use App\Domains\Employee\Models\Employee;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerformanceCycleParticipant extends PerformanceModel
{
    protected $fillable = [
        'tenant_id',
        'cycle_id',
        'employee_id',
        'manager_id',
        'status',
        'eligible_from',
        'eligible_to',
    ];

    protected function casts(): array
    {
        return [
            'eligible_from' => 'date',
            'eligible_to' => 'date',
        ];
    }

    public function cycle(): BelongsTo
    {
        return $this->belongsTo(PerformanceCycle::class, 'cycle_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }
}
