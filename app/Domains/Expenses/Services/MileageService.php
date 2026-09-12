<?php

namespace App\Domains\Expenses\Services;

use App\Domains\Expenses\Models\MileageRate;
use Carbon\Carbon;

class MileageService
{
    public function getActiveRate(string $tenantId, string $vehicleType = 'standard_car', ?string $location = null, ?string $jobGradeId = null, ?string $date = null): float
    {
        $queryDate = $date ? Carbon::parse($date)->toDateString() : now()->toDateString();

        $rateRecord = MileageRate::query()
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->where(function ($q) use ($vehicleType) {
                $q->where('vehicle_type', $vehicleType)->orWhereNull('vehicle_type');
            })
            ->where(function ($q) use ($location) {
                if ($location) {
                    $q->where('location', $location)->orWhereNull('location');
                }
            })
            ->where(function ($q) use ($jobGradeId) {
                if ($jobGradeId) {
                    $q->where('job_grade_id', $jobGradeId)->orWhereNull('job_grade_id');
                }
            })
            ->whereDate('effective_from', '<=', $queryDate)
            ->where(function ($q) use ($queryDate) {
                $q->whereNull('effective_to')->orWhereDate('effective_to', '>=', $queryDate);
            })
            ->orderBy('job_grade_id', 'desc')
            ->orderBy('location', 'desc')
            ->orderBy('effective_from', 'desc')
            ->first();

        return $rateRecord ? (float) $rateRecord->rate_per_unit : 100.0000;
    }

    public function calculateMileageAmount(float $distance, float $ratePerUnit): float
    {
        return round(max(0.0, $distance) * $ratePerUnit, 4);
    }
}
