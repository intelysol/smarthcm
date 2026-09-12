<?php

declare(strict_types=1);

namespace App\Domains\Compensation\Services;

class CompensationCalculationEngine
{
    /**
     * Calculate Compa-Ratio = Base Salary / Midpoint
     */
    public function calculateCompaRatio(float $baseSalary, float $midpoint): float
    {
        if ($midpoint <= 0.0) {
            return 0.0;
        }

        return round($baseSalary / $midpoint, 4);
    }

    /**
     * Calculate Range Penetration = (Base Salary - Minimum) / (Maximum - Minimum)
     * Value typically ranges from 0.0 (at min) to 1.0 (at max), but can be < 0 or > 1.
     */
    public function calculateRangePenetration(float $baseSalary, float $minimum, float $maximum): float
    {
        $range = $maximum - $minimum;
        if ($range <= 0.0) {
            return 0.0;
        }

        return round(($baseSalary - $minimum) / $range, 4);
    }

    /**
     * Determine Compa-Ratio Bracket category
     */
    public function getCompaRatioBracket(float $compaRatio): string
    {
        if ($compaRatio < 0.80) {
            return 'under_80';
        }
        if ($compaRatio <= 0.95) {
            return '80_to_95';
        }
        if ($compaRatio <= 1.05) {
            return '95_to_105';
        }
        if ($compaRatio <= 1.20) {
            return '105_to_120';
        }

        return 'over_120';
    }

    /**
     * Lookup recommended increase % from Merit Matrix Grid
     * Grid format: [ ['rating' => 'exceeds', 'bracket' => '80_to_95', 'min_pct' => 4.0, 'target_pct' => 6.0, 'max_pct' => 8.0] ]
     */
    public function lookupMeritMatrixGuideline(array $grid, string $rating, float $compaRatio): array
    {
        $bracket = $this->getCompaRatioBracket($compaRatio);
        $normalizedRating = strtolower(trim($rating));

        foreach ($grid as $cell) {
            $cellRating = strtolower(trim((string) ($cell['rating'] ?? '')));
            $cellBracket = (string) ($cell['bracket'] ?? '');

            if ($cellRating === $normalizedRating && ($cellBracket === $bracket || $cellBracket === 'all')) {
                return [
                    'bracket' => $bracket,
                    'min_pct' => (float) ($cell['min_pct'] ?? 0.0),
                    'target_pct' => (float) ($cell['target_pct'] ?? 0.0),
                    'max_pct' => (float) ($cell['max_pct'] ?? 0.0),
                ];
            }
        }

        // Default standard guidelines fallback based on rating
        $defaultMap = [
            'outstanding' => ['min_pct' => 5.0, 'target_pct' => 7.0, 'max_pct' => 10.0],
            'exceeds' => ['min_pct' => 3.5, 'target_pct' => 5.0, 'max_pct' => 7.0],
            'meets' => ['min_pct' => 2.0, 'target_pct' => 3.0, 'max_pct' => 4.5],
            'needs_improvement' => ['min_pct' => 0.0, 'target_pct' => 0.0, 'max_pct' => 1.5],
            'unsatisfactory' => ['min_pct' => 0.0, 'target_pct' => 0.0, 'max_pct' => 0.0],
        ];

        $fallback = $defaultMap[$normalizedRating] ?? ['min_pct' => 0.0, 'target_pct' => 2.5, 'max_pct' => 5.0];

        // If compa-ratio is over 120, compress increase by 25%; if under 80, expand by 25%
        $multiplier = 1.0;
        if ($compaRatio > 1.20) {
            $multiplier = 0.75;
        } elseif ($compaRatio < 0.80) {
            $multiplier = 1.25;
        }

        return [
            'bracket' => $bracket,
            'min_pct' => round($fallback['min_pct'] * $multiplier, 2),
            'target_pct' => round($fallback['target_pct'] * $multiplier, 2),
            'max_pct' => round($fallback['max_pct'] * $multiplier, 2),
        ];
    }

    /**
     * Compute New Base Salary & Increase Amount given base salary and percentage
     */
    public function computeSalaryFromPercentage(float $currentBaseSalary, float $percentage): array
    {
        $increaseAmount = round($currentBaseSalary * ($percentage / 100.0), 2);
        $newBaseSalary = round($currentBaseSalary + $increaseAmount, 2);

        return [
            'increase_amount' => $increaseAmount,
            'recommended_base_salary' => $newBaseSalary,
        ];
    }

    /**
     * Calculate Bonus Amount = Base Salary × Target % × Individual Multiplier × Company Multiplier
     */
    public function calculateIncentiveBonus(
        float $baseSalary,
        float $targetBonusPct,
        float $individualMultiplier = 1.0,
        float $companyMultiplier = 1.0
    ): float {
        $targetBonus = $baseSalary * ($targetBonusPct / 100.0);
        $bonus = $targetBonus * $individualMultiplier * $companyMultiplier;

        return round($bonus, 2);
    }
}
