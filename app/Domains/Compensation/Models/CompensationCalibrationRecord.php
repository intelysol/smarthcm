<?php

namespace App\Domains\Compensation\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompensationCalibrationRecord extends CompensationModel
{
    protected $fillable = [
        'tenant_id',
        'compensation_calibration_session_id',
        'compensation_recommendation_id',
        'original_increase_pct',
        'original_increase_amount',
        'calibrated_increase_pct',
        'calibrated_increase_amount',
        'mandatory_calibration_reason',
        'calibrated_by',
        'calibrated_at',
    ];

    protected function casts(): array
    {
        return [
            'original_increase_pct' => 'decimal:4',
            'original_increase_amount' => 'decimal:2',
            'calibrated_increase_pct' => 'decimal:4',
            'calibrated_increase_amount' => 'decimal:2',
            'calibrated_at' => 'datetime',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(CompensationCalibrationSession::class, 'compensation_calibration_session_id');
    }

    public function recommendation(): BelongsTo
    {
        return $this->belongsTo(CompensationRecommendation::class, 'compensation_recommendation_id');
    }

    public function calibratedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'calibrated_by');
    }
}
