<?php

namespace App\Domains\Career\Services;

use App\Domains\Career\Models\CareerPathStep;
use App\Domains\Career\Models\EmployeeSkill;
use App\Domains\Employee\Models\Employee;
use App\Domains\Learning\Models\EmployeeLearningRecord;
use App\Domains\Learning\Models\LearningCertificate;
use App\Domains\Performance\Models\PerformanceCompetencyAssessment;
use App\Domains\Performance\Models\PerformanceFinalOutcome;
use Carbon\Carbon;

class CareerEligibilityService
{
    public function evaluateStepEligibility(Employee $employee, CareerPathStep $step): array
    {
        $unmetCriteria = [];
        $details = [];

        // 1. Experience / Tenure Check
        $tenureYears = $employee->joining_date
            ? Carbon::parse($employee->joining_date)->diffInDays(now()) / 365.25
            : 0.0;

        $minExpYears = (float) $step->minimum_experience_years;
        $details['experience'] = [
            'required' => $minExpYears,
            'current' => round($tenureYears, 1),
            'satisfied' => ($tenureYears >= $minExpYears),
        ];
        if ($tenureYears < $minExpYears) {
            $unmetCriteria[] = "Requires at least {$minExpYears} years of experience (current: " . round($tenureYears, 1) . " years).";
        }

        // 2. Performance Rating Check
        $latestOutcome = PerformanceFinalOutcome::query()
            ->where('tenant_id', $employee->tenant_id)
            ->where('employee_id', $employee->id)
            ->latest('finalized_at')
            ->first();

        $currentRating = $latestOutcome ? (float) $latestOutcome->final_rating : 3.0;
        $minRating = $step->performance_min_rating !== null ? (float) $step->performance_min_rating : null;

        if ($minRating !== null) {
            $details['performance'] = [
                'required' => $minRating,
                'current' => $currentRating,
                'satisfied' => ($currentRating >= $minRating),
            ];
            if ($currentRating < $minRating) {
                $unmetCriteria[] = "Requires minimum performance rating of {$minRating} (current: {$currentRating}).";
            }
        }

        // 3. Required Skills Check
        $requiredSkills = $step->required_skills ?? [];
        $employeeSkills = EmployeeSkill::query()
            ->where('tenant_id', $employee->tenant_id)
            ->where('employee_id', $employee->id)
            ->pluck('current_level', 'skill_id');

        $missingSkills = [];
        foreach ($requiredSkills as $skillId => $reqLevel) {
            $currentLvl = (int) ($employeeSkills[$skillId] ?? 0);
            if ($currentLvl < (int) $reqLevel) {
                $missingSkills[] = [
                    'skill_id' => $skillId,
                    'required' => (int) $reqLevel,
                    'current' => $currentLvl,
                ];
            }
        }
        $details['skills'] = [
            'total_required' => count($requiredSkills),
            'missing' => $missingSkills,
            'satisfied' => empty($missingSkills),
        ];
        if (! empty($missingSkills)) {
            $unmetCriteria[] = count($missingSkills) . " required skills not yet satisfied.";
        }

        // 4. Required Certifications / Learning Check
        $requiredCerts = $step->required_certifications ?? [];
        $earnedCertCourseIds = LearningCertificate::query()
            ->where('tenant_id', $employee->tenant_id)
            ->where('employee_id', $employee->id)
            ->where('status', 'active')
            ->pluck('course_id')
            ->all();

        $missingCerts = [];
        foreach ($requiredCerts as $courseId) {
            if (! in_array($courseId, $earnedCertCourseIds, true)) {
                $missingCerts[] = $courseId;
            }
        }
        $details['certifications'] = [
            'required' => $requiredCerts,
            'missing' => $missingCerts,
            'satisfied' => empty($missingCerts),
        ];
        if (! empty($missingCerts)) {
            $unmetCriteria[] = count($missingCerts) . " mandatory certifications/courses missing.";
        }

        $isEligible = empty($unmetCriteria);

        return [
            'eligible' => $isEligible,
            'unmet_criteria' => $unmetCriteria,
            'details' => $details,
        ];
    }
}
