<?php

namespace App\Domains\Compensation\Models;

use App\Domains\Employee\Models\Employee;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BonusAllocation extends Model
{
    use HasUuids;

    protected $fillable = [
        'compensation_cycle_id',
        'employee_id',
        'bonus_type',
        'amount',
        'calculation_factors',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'calculation_factors' => 'array',
        ];
    }

    public function cycle(): BelongsTo
    {
        return $this->belongsTo(CompensationCycle::class, 'compensation_cycle_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
