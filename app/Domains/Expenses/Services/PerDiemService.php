<?php

namespace App\Domains\Expenses\Services;

use App\Domains\Expenses\Models\PerDiemRate;
use Carbon\Carbon;

class PerDiemService
{
    public function getActiveRate(string $tenantId, string $destinationType = 'domestic', ?string $country = null, ?string $city = null, ?string $jobGradeId = null, ?string $date = null): PerDiemRate
    {
        $queryDate = $date ? Carbon::parse($date)->toDateString() : now()->toDateString();

        $rateRecord = PerDiemRate::query()
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->where(function ($q) use ($destinationType) {
                $q->where('destination_type', $destinationType)->orWhereNull('destination_type');
            })
            ->whereDate('effective_from', '<=', $queryDate)
            ->where(function ($q) use ($queryDate) {
                $q->whereNull('effective_to')->orWhereDate('effective_to', '>=', $queryDate);
            })
            ->orderBy('destination_city', 'desc')
            ->orderBy('destination_country', 'desc')
            ->orderBy('job_grade_id', 'desc')
            ->first();

        if ($rateRecord) {
            return $rateRecord;
        }

        // Fallback transient instance
        $fallback = new PerDiemRate();
        $fallback->daily_rate = 100.0000;
        $fallback->currency = 'USD';
        $fallback->departure_day_percentage = 75.00;
        $fallback->return_day_percentage = 75.00;
        $fallback->breakfast_deduction_percentage = 20.00;
        $fallback->lunch_deduction_percentage = 30.00;
        $fallback->dinner_deduction_percentage = 30.00;
        return $fallback;
    }

    public function calculatePerDiemAmount(
        PerDiemRate $rate,
        float $days,
        bool $isDepartureDay = false,
        bool $isReturnDay = false,
        bool $breakfastProvided = false,
        bool $lunchProvided = false,
        bool $dinnerProvided = false
    ): array {
        $dailyRate = (float) $rate->daily_rate;
        $baseTotal = $dailyRate * $days;

        // Apply partial day factor if 1 day trip departure/return
        $dayFactor = 1.0;
        if ($isDepartureDay && !$isReturnDay) {
            $dayFactor = (float) $rate->departure_day_percentage / 100;
        } elseif ($isReturnDay && !$isDepartureDay) {
            $dayFactor = (float) $rate->return_day_percentage / 100;
        } elseif ($isDepartureDay && $isReturnDay) {
            $dayFactor = min(1.0, ((float) $rate->departure_day_percentage + (float) $rate->return_day_percentage) / 200);
        }

        $gross = round($baseTotal * $dayFactor, 4);

        // Calculate Meal Deductions
        $deductionPercent = 0.0;
        if ($breakfastProvided) {
            $deductionPercent += (float) $rate->breakfast_deduction_percentage;
        }
        if ($lunchProvided) {
            $deductionPercent += (float) $rate->lunch_deduction_percentage;
        }
        if ($dinnerProvided) {
            $deductionPercent += (float) $rate->dinner_deduction_percentage;
        }

        $deductions = round($gross * min(1.0, $deductionPercent / 100), 4);
        $netPerDiem = max(0.0, $gross - $deductions);

        return [
            'gross_amount' => $gross,
            'deduction_amount' => $deductions,
            'net_eligible_amount' => $netPerDiem,
            'daily_rate' => $dailyRate,
            'currency' => $rate->currency,
        ];
    }
}
