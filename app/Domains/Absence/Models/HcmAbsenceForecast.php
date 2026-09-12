<?php

namespace App\Domains\Absence\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmAbsenceForecast extends Model
{
    use HasUuids;

    protected $table = 'hcm_absence_forecasts';

    protected $fillable = [
        'tenant_id',
        'forecast_period_start',
        'forecast_period_end',
        'department_id',
        'projected_absence_hours',
        'projected_absence_rate',
        'confidence_score',
        'forecast_method',
        'assumptions',
        'generated_at',
    ];

    protected $casts = [
        'forecast_period_start' => 'date',
        'forecast_period_end' => 'date',
        'projected_absence_hours' => 'decimal:2',
        'projected_absence_rate' => 'decimal:2',
        'confidence_score' => 'decimal:2',
        'assumptions' => 'array',
        'generated_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}