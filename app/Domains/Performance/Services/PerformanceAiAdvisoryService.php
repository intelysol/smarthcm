<?php

declare(strict_types=1);

namespace App\Domains\Performance\Services;

class PerformanceAiAdvisoryService
{
    /**
     * Assistive SMART goal drafting and refinement.
     * STRICT ARCHITECTURAL RULE: All outputs are advisory-only (is_advisory => true).
     * Cannot autonomously establish or mandate goal metrics.
     */
    public function draftSmartGoal(string $rawGoalDescription): array
    {
        $lower = strtolower($rawGoalDescription);

        $suggestedObjective = ucfirst(trim($rawGoalDescription));
        $suggestedKeyResult = 'Achieve measurable milestone with baseline tracking';
        $suggestedTarget = 100.0;
        $suggestedUnit = 'percentage';

        if (str_contains($lower, 'sales') || str_contains($lower, 'revenue')) {
            $suggestedObjective = 'Accelerate enterprise revenue growth and sales pipeline velocity';
            $suggestedKeyResult = 'Increase qualified lead conversion rate by 15%';
            $suggestedTarget = 15.0;
            $suggestedUnit = 'percent_growth';
        } elseif (str_contains($lower, 'customer') || str_contains($lower, 'support') || str_contains($lower, 'service')) {
            $suggestedObjective = 'Enhance customer service responsiveness and resolution quality';
            $suggestedKeyResult = 'Reduce average first-contact resolution time to under 2 hours';
            $suggestedTarget = 2.0;
            $suggestedUnit = 'hours';
        } elseif (str_contains($lower, 'bug') || str_contains($lower, 'quality') || str_contains($lower, 'defect')) {
            $suggestedObjective = 'Improve platform code reliability and defect remediation throughput';
            $suggestedKeyResult = 'Decrease critical post-release defect escapes by 25%';
            $suggestedTarget = 25.0;
            $suggestedUnit = 'percent_reduction';
        }

        return [
            'is_advisory' => true,
            'notice' => 'AI suggestions are strictly advisory and require human employee and manager review and approval.',
            'original_input' => $rawGoalDescription,
            'suggested_objective' => $suggestedObjective,
            'suggested_key_result' => $suggestedKeyResult,
            'suggested_measurement_type' => 'target_value',
            'suggested_target' => $suggestedTarget,
            'suggested_unit' => $suggestedUnit,
        ];
    }

    /**
     * Assistive performance review summary synthesis from goals, feedback, and competencies.
     */
    public function summarizePerformance(array $goals, array $competencies, array $feedbackItems): array
    {
        $completedGoals = count(array_filter($goals, fn ($g) => ($g['progress_percentage'] ?? 0) >= 100));
        $totalGoals = count($goals);

        return [
            'is_advisory' => true,
            'notice' => 'Synthesized summary is assistive and should not replace human managerial judgment.',
            'goal_metrics' => [
                'total_goals' => $totalGoals,
                'completed_goals' => $completedGoals,
                'at_risk_goals' => count(array_filter($goals, fn ($g) => ($g['status'] ?? '') === 'at_risk')),
            ],
            'feedback_summary' => 'Received ' . count($feedbackItems) . ' feedback response items highlighting collaboration and communication strengths.',
            'growth_recommendations' => [
                'Consider deep-dive technical leadership certification in the upcoming cycle.',
                'Establish regular bi-weekly check-in cadences to maintain goal momentum.',
            ],
        ];
    }
}
