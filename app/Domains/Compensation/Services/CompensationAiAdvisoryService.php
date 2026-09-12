<?php

declare(strict_types=1);

namespace App\Domains\Compensation\Services;

class CompensationAiAdvisoryService
{
    /**
     * Provide advisory-only justification assistance for manager merit proposals
     * Strict compliance rule: must include 'is_advisory' => true
     */
    public function draftMeritJustification(
        string $employeeName,
        string $performanceRating,
        float $compaRatio,
        float $proposedPercentage,
        ?string $additionalContext = null
    ): array {
        $bracket = $compaRatio < 0.8 ? 'below band midpoint' : ($compaRatio > 1.2 ? 'at high end of salary band' : 'aligned with market midpoint');

        $suggestion = "Employee {$employeeName} has demonstrated {$performanceRating} performance during this review cycle. "
            . "With a current compa-ratio of " . round($compaRatio, 2) . " ({$bracket}), a merit increase of {$proposedPercentage}% "
            . "is recommended to ensure retention, reward critical contributions, and maintain equitable market positioning.";

        if ($additionalContext) {
            $suggestion .= " Notable highlight: {$additionalContext}.";
        }

        return [
            'is_advisory' => true,
            'confidence_score' => 0.92,
            'suggested_justification' => $suggestion,
            'advisory_notes' => 'Managers and HR calibration committees retain final decision-making authority.',
        ];
    }

    /**
     * Provide advisory-only budget scenario modeling explanation
     */
    public function explainBudgetVariance(
        float $allocated,
        float $proposed,
        float $variance
    ): array {
        $status = $variance >= 0 ? 'within allocation' : 'over allocation';
        $pct = $allocated > 0 ? round(abs($variance) / $allocated * 100, 1) : 0;

        $explanation = "The department proposals total " . number_format($proposed, 2) . " against a budget of "
            . number_format($allocated, 2) . ", which is {$pct}% {$status}.";

        return [
            'is_advisory' => true,
            'variance_amount' => $variance,
            'status' => $status,
            'summary_narrative' => $explanation,
        ];
    }
}
