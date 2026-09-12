<?php

namespace App\Domains\Benefits\Services;

use App\Domains\Benefits\Models\BenefitEnrollmentWindow;
use App\Domains\Benefits\Models\BenefitPlan;
use App\Domains\Employee\Models\Employee;
use Illuminate\Database\Eloquent\Collection;

class BenefitsAiAdvisoryService
{
    public function __construct(
        protected BenefitEligibilityService $eligibilityService,
        protected BenefitOpenEnrollmentService $openEnrollmentService
    ) {}

    /**
     * Answer employee queries regarding available eligible plans.
     * Only returns factual, authorized data without medical advice.
     */
    public function explainAvailablePlans(Employee $employee): array
    {
        $eligiblePlans = $this->eligibilityService->getEligiblePlansForEmployee($employee);

        $plansData = $eligiblePlans->map(function (BenefitPlan $plan) {
            return [
                'plan_id' => $plan->id,
                'name' => $plan->name,
                'code' => $plan->code,
                'benefit_type' => $plan->benefit_type,
                'is_mandatory' => (bool) $plan->is_mandatory,
                'is_waivable' => (bool) $plan->is_waivable,
                'coverage_level' => $plan->coverage_level,
                'estimated_employee_cost' => (float) $plan->employee_cost,
                'estimated_employer_cost' => (float) $plan->employer_cost,
                'currency' => $plan->currency,
                'waiting_period_days' => (int) $plan->waiting_period_days,
            ];
        })->toArray();

        return [
            'summary' => "You are currently eligible for " . count($plansData) . " benefit plan(s) based on your employment profile.",
            'eligible_plans' => $plansData,
            'disclaimer' => "This is a factual summary of your configured organizational benefits. This assistant does not provide medical advice or evaluate health conditions.",
        ];
    }

    /**
     * Generate factual side-by-side comparison between 2 or more plans.
     */
    public function comparePlans(array $planIds, Employee $employee): array
    {
        $plans = BenefitPlan::where('tenant_id', $employee->tenant_id)
            ->whereIn('id', $planIds)
            ->with(['coverages', 'provider', 'category'])
            ->get();

        $comparison = [];
        foreach ($plans as $plan) {
            $comparison[] = [
                'id' => $plan->id,
                'name' => $plan->name,
                'category' => $plan->category?->name ?? 'Standard',
                'provider' => $plan->provider?->name ?? 'Internal / Employer Sponsored',
                'employee_cost_monthly' => (float) $plan->employee_cost,
                'employer_cost_monthly' => (float) $plan->employer_cost,
                'annual_limit' => $plan->annual_limit ? (float) $plan->annual_limit : 'Unlimited / None',
                'waiting_period_days' => (int) $plan->waiting_period_days,
                'is_mandatory' => (bool) $plan->is_mandatory,
                'coverage_tiers' => $plan->coverages->pluck('name')->toArray(),
            ];
        }

        return [
            'comparison' => $comparison,
            'factual_notes' => "Comparison generated purely from active plan configurations. No subjective ratings or health determinations are made.",
            'disclaimer' => "Consult your benefits administrator or policy handbook for comprehensive terms. Antigravity AI does not offer medical advice.",
        ];
    }

    /**
     * Explain why a specific plan appears eligible or not eligible.
     */
    public function explainEligibility(Employee $employee, BenefitPlan $plan): array
    {
        $result = $this->eligibilityService->evaluateEligibility($employee, $plan, null, false);

        return [
            'plan_name' => $plan->name,
            'status' => $result->status,
            'effective_date' => $result->effective_date?->toDateString(),
            'explanation' => $result->reason,
            'criteria_trace' => $result->criteria_evaluation,
        ];
    }

    /**
     * Process general chat message with strict safety guardrails against medical diagnosis.
     */
    public function processAdvisoryPrompt(string $prompt, Employee $employee): array
    {
        $lowerPrompt = strtolower($prompt);

        // Enforce safety boundary: Reject medical advice or health condition inquiries
        $medicalKeywords = ['diagnos', 'symptom', 'disease', 'condition', 'treatment', 'doctor recommend', 'medical advice', 'prescription recommendation'];
        foreach ($medicalKeywords as $keyword) {
            if (str_contains($lowerPrompt, $keyword)) {
                return [
                    'type' => 'prohibited_topic',
                    'message' => "I cannot provide medical advice, diagnose health conditions, or recommend insurance plans based on personal symptoms or medical conditions. Please consult a qualified medical professional for health guidance, or your HR Benefits team for plan options.",
                    'is_medical_advice_prevented' => true,
                ];
            }
        }

        if (str_contains($lowerPrompt, 'eligible') || str_contains($lowerPrompt, 'what benefit') || str_contains($lowerPrompt, 'available')) {
            return $this->explainAvailablePlans($employee);
        }

        return [
            'type' => 'general_advisory',
            'message' => "You can view your available plans, compare options side-by-side, check open enrollment deadlines, or report qualifying life events through the Benefits portal.",
            'disclaimer' => "Antigravity Advisory Service is strictly informative and adheres to configured HCM policies.",
        ];
    }
}
