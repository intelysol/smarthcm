<?php

declare(strict_types=1);

namespace App\Domains\Performance\Models;

use App\Domains\Employee\Models\Employee;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerformanceCheckin extends PerformanceModel
{
    protected $fillable = [
        'tenant_id',
        'cycle_id',
        'employee_id',
        'manager_id',
        'scheduled_at',
        'completed_at',
        'summary',
        'employee_comment',
        'manager_comment',
        'agenda_items',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'completed_at' => 'datetime',
            'agenda_items' => 'array',
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
