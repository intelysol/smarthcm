<?php

declare(strict_types=1);

namespace App\Domains\Compensation\Services;

use App\Domains\Compensation\Models\CompensationCycle;

class PayEquityAnalyticsService
{
    /**
     * Compute aggregated pay equity metrics by gender and job grade
     * Strict privacy & protection of individual demographic data
     */
    public function analyzeCycleEquity(CompensationCycle $cycle): array
    {
        $recommendations = $cycle->recommendations()
            ->with(['employee'])
            ->get();

        if ($recommendations->isEmpty()) {
            return [
                'total_reviewed' => 0,
                'overall_average_compa_ratio' => 0.0,
                'gender_analysis' => [],
                'grade_analysis' => [],
                'pay_gap_percentage' => 0.0,
                'outliers_count' => 0,
            ];
        }

        $genderGroups = [];
        $gradeGroups = [];
        $outliersCount = 0;

        foreach ($recommendations as $rec) {
            $gender = $rec->employee ? ($rec->employee->gender ?? 'unspecified') : 'unspecified';
            $grade = $rec->job_grade_id ?? 'default_grade';
            $compa = (float) $rec->compa_ratio_current;
            $newSalary = (float) $rec->recommended_base_salary;
            $increasePct = (float) $rec->increase_percentage;

            if ($compa < 0.80 || $compa > 1.25) {
                $outliersCount++;
            }

            // Gender rollup
            if (! isset($genderGroups[$gender])) {
                $genderGroups[$gender] = ['count' => 0, 'total_salary' => 0.0, 'total_compa' => 0.0, 'total_increase_pct' => 0.0];
            }
            $genderGroups[$gender]['count']++;
            $genderGroups[$gender]['total_salary'] += $newSalary;
            $genderGroups[$gender]['total_compa'] += $compa;
            $genderGroups[$gender]['total_increase_pct'] += $increasePct;

            // Grade rollup
            if (! isset($gradeGroups[$grade])) {
                $gradeGroups[$grade] = ['count' => 0, 'total_salary' => 0.0, 'total_compa' => 0.0];
            }
            $gradeGroups[$grade]['count']++;
            $gradeGroups[$grade]['total_salary'] += $newSalary;
            $gradeGroups[$grade]['total_compa'] += $compa;
        }

        $genderReport = [];
        foreach ($genderGroups as $g => $data) {
            $genderReport[$g] = [
                'count' => $data['count'],
                'average_salary' => round($data['total_salary'] / $data['count'], 2),
                'average_compa_ratio' => round($data['total_compa'] / $data['count'], 4),
                'average_increase_pct' => round($data['total_increase_pct'] / $data['count'], 2),
            ];
        }

        $maleAvg = $genderReport['male']['average_salary'] ?? 0.0;
        $femaleAvg = $genderReport['female']['average_salary'] ?? 0.0;
        $payGapPct = 0.0;
        if ($maleAvg > 0.0 && $femaleAvg > 0.0) {
            $payGapPct = round((($maleAvg - $femaleAvg) / $maleAvg) * 100, 2);
        }

        $gradeReport = [];
        foreach ($gradeGroups as $gr => $data) {
            $gradeReport[$gr] = [
                'count' => $data['count'],
                'average_salary' => round($data['total_salary'] / $data['count'], 2),
                'average_compa_ratio' => round($data['total_compa'] / $data['count'], 4),
            ];
        }

        $totalRecs = $recommendations->count();
        $overallAvgCompa = round($recommendations->avg('compa_ratio_current') ?? 1.0, 4);

        return [
            'total_reviewed' => $totalRecs,
            'overall_average_compa_ratio' => $overallAvgCompa,
            'gender_analysis' => $genderReport,
            'grade_analysis' => $gradeReport,
            'pay_gap_percentage' => $payGapPct,
            'outliers_count' => $outliersCount,
        ];
    }
}
