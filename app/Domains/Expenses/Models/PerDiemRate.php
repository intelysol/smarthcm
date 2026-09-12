<?php

namespace App\Domains\Expenses\Models;

use App\Domains\Organization\Models\JobGrade;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerDiemRate extends Model
{
    use HasUuids;

    protected $table = 'per_diem_rates';

    protected $fillable = [
        'tenant_id',
        'destination_type',
        'destination_country',
        'destination_city',
        'job_grade_id',
        'daily_rate',
        'currency',
        'departure_day_percentage',
        'return_day_percentage',
        'breakfast_deduction_percentage',
        'lunch_deduction_percentage',
        'dinner_deduction_percentage',
        'effective_from',
        'effective_to',
        'is_active',
    ];

    protected $casts = [
        'daily_rate' => 'decimal:4',
        'departure_day_percentage' => 'decimal:2',
        'return_day_percentage' => 'decimal:2',
        'breakfast_deduction_percentage' => 'decimal:2',
        'lunch_deduction_percentage' => 'decimal:2',
        'dinner_deduction_percentage' => 'decimal:2',
        'effective_from' => 'date',
        'effective_to' => 'date',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function jobGrade(): BelongsTo
    {
        return $this->belongsTo(JobGrade::class);
    }
}
