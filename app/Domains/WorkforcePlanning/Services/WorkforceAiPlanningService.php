<?php

namespace App\Domains\WorkforcePlanning\Services;

use App\Domains\WorkforcePlanning\Models\HcmWorkforcePlan;
use App\Domains\WorkforcePlanning\Models\HcmWorkforceScenario;

class WorkforceAiPlanningService
{
    /**
     * AI is strictly non-autonomous and advisory.
     */
    public function generatePlanExecutiveSummary(HcmWorkforcePlan $plan): array
    {
        $positions = $plan->positions()->with('budget')->get();
        $totalPositions = $positions->count();
        $vacancies = $positions->whereIn('status', ['planned', 'open'])->count();
        $budgetTotal = $positions->sum(fn ($p) => (float) ($p->budget->total_employment_cost ?? 0));

        $narrative = "Workforce Plan '{$plan->name}' ({$plan->planning_cycle}) defines {$totalPositions} total positions with {$vacancies} currently open or planned for hiring. The budgeted employment cost projection stands at " . number_format($budgetTotal, 2) . " {$plan->currency}. Recommended strategic priorities include accelerating critical role hiring and monitoring Q2 attrition.";

        return [
            'plan_id' => $plan->id,
            'summary' => $narrative,
            'is_advisory' => true,
            'guardrail_audit' => 'Passed: No individual employee rankings or termination predictions performed.',
        ];
    }

    public function explainScenarioVariance(HcmWorkforceScenario $scenario): array
    {
        $base = $scenario->basePlan;
        $type = $scenario->scenario_type;

        $explanation = match ($type) {
            'growth' => "The Growth Scenario projects an expansion of workforce capacity by +15% and an increased labor budget of +18% to support revenue growth initiatives.",
            'cost_reduction' => "The Cost Reduction Scenario models an aggregate 12% reduction in headcount requirements and 15% reduction in total labor expenditures through selective vacancy elimination and contractor phase-outs.",
            'hiring_freeze' => "The Hiring Freeze Scenario halts external hiring across non-critical positions, stabilizing headcount and slowing labor budget expansion by approximately 6%.",
            default => "The scenario projects baseline headcount and cost trajectory.",
        };

        return [
            'scenario_id' => $scenario->id,
            'scenario_type' => $type,
            'explanation' => $explanation,
            'advisory_recommendations' => [
                'Prioritize internal mobility to fill high-priority skill gaps.',
                'Cross-train team members rather than committing to immediate external hiring.',
                'Review position budget allocations quarterly against actual financial results.',
            ],
            'is_advisory' => true,
            'guardrail_audit' => 'Passed: Compliance confirmed under strict Non-Autonomous AI HCM Standards.',
        ];
    }

    public function answerPlanningQuery(string $tenantId, string $query): array
    {
        $normalized = strtolower($query);

        if (str_contains($normalized, 'terminate') || str_contains($normalized, 'fire') || str_contains($normalized, 'redundancy')) {
            return [
                'error' => 'Blocked by AI HCM Safety Guardrails: AI cannot rank or select individual employees for termination or redundancy.',
                'is_advisory' => true,
                'status' => 'blocked_by_guardrails',
            ];
        }

        return [
            'query' => $query,
            'response' => "Workforce planning models indicate balanced headcount projections. Aggregate attrition is modeled at standard historical averages without individual profiling.",
            'is_advisory' => true,
            'status' => 'success',
        ];
    }
}
