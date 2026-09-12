<?php

namespace App\Domains\Expenses\Models;

use App\Domains\Organization\Models\JobGrade;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MileageRate extends Model
{
    use HasUuids;

    protected $table = 'mileage_rates';

    protected $fillable = [
        'tenant_id',
        'vehicle_type',
        'location',
        'job_grade_id',
        'rate_per_unit',
        'unit',
        'currency',
        'effective_from',
        'effective_to',
        'is_active',
    ];

    protected $casts = [
        'rate_per_unit' => 'decimal:4',
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
