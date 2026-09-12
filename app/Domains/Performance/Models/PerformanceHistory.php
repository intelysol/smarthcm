<?php

declare(strict_types=1);

namespace App\Domains\Performance\Models;

use App\Domains\Employee\Models\Employee;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerformanceHistory extends PerformanceModel
{
    protected $table = 'performance_history';

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'cycle_id',
        'event_type',
        'snapshot',
        'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'snapshot' => 'array',
            'recorded_at' => 'datetime',
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
}
