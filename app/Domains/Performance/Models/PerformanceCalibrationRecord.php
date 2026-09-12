<?php

declare(strict_types=1);

namespace App\Domains\Performance\Models;

use App\Domains\Employee\Models\Employee;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerformanceCalibrationRecord extends Model
{
    use HasUuids;

    protected $fillable = [
        'session_id',
        'employee_id',
        'calculated_rating',
        'proposed_rating',
        'final_rating',
        'change_reason',
        'changed_by',
        'changed_at',
    ];

    protected function casts(): array
    {
        return [
            'calculated_rating' => 'decimal:4',
            'proposed_rating' => 'decimal:4',
            'final_rating' => 'decimal:4',
            'changed_at' => 'datetime',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(PerformanceCalibrationSession::class, 'session_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
