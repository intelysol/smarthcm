<?php

namespace App\Domains\Expenses\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\Expenses\Enums\AdvanceStatus;
use App\Domains\Expenses\Models\ExpenseCategory;
use App\Domains\Expenses\Models\ExpenseClaim;
use App\Domains\Expenses\Models\ExpensePolicy;
use App\Domains\Expenses\Models\PerDiemRate;
use App\Domains\Expenses\Models\TravelAdvance;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ExpenseAiAdvisoryService
{
    public const ADVISORY_DISCLAIMER = 'This guidance is strictly advisory. Official expense reimbursement, travel authorizations, and policy overrides require designated manager and finance approvals.';

    public function __construct(
        protected ExpensePolicyService $policyService,
        protected PerDiemService $perDiemService,
        protected TravelAdvanceService $advanceService,
        protected ReceiptService $receiptService
    ) {}

    /**
     * Provide comprehensive, human-readable policy guidance for an employee and category.
     */
    public function explainPolicyRules(string $tenantId, ?string $categoryCode = null, ?Employee $employee = null): array
    {
        $policy = null;
        if ($employee) {
            $policy = $this->policyService->resolvePolicyForEmployee($employee);
        }

        if (! $policy) {
            $policy = ExpensePolicy::where('tenant_id', $tenantId)->where('status', 'active')->first();
        }

        $activeVersion = $policy ? $this->policyService->getActiveVersionForDate($policy, now()->toDateString()) : null;

        $categoriesQuery = ExpenseCategory::where('tenant_id', $tenantId)->where('is_active', true);
        if ($categoryCode) {
            $categoriesQuery->where('code', $categoryCode);
        }
        $categories = $categoriesQuery->get();

        $categorySummaries = $categories->map(function (ExpenseCategory $cat) {
            return [
                'code' => $cat->code,
                'name' => $cat->name,
                'category_type' => $cat->category_type,
                'max_amount' => $cat->max_amount ? (float) $cat->max_amount : null,
                'receipt_required' => (bool) $cat->receipt_required,
                'receipt_threshold' => (float) $cat->receipt_threshold,
                'is_reimbursable' => (bool) $cat->is_reimbursable,
                'tax_treatment' => $cat->tax_treatment,
                'guidance' => $this->generateCategoryGuidance($cat),
            ];
        })->toArray();

        return [
            'is_advisory' => true,
            'disclaimer' => self::ADVISORY_DISCLAIMER,
            'policy_name' => $policy?->name ?? 'Default Corporate Travel & Expense Policy',
            'daily_meal_limit' => $activeVersion?->daily_meal_limit ? (float) $activeVersion->daily_meal_limit : null,
            'daily_hotel_limit' => $activeVersion?->daily_hotel_limit ? (float) $activeVersion->daily_hotel_limit : null,
            'receipt_required_threshold' => $activeVersion?->receipt_required_threshold ? (float) $activeVersion->receipt_required_threshold : 0.0,
            'allow_policy_override' => $activeVersion?->allow_policy_override ?? true,
            'categories' => $categorySummaries,
            'general_recommendations' => [
                'Submit expense claims within 30 days of expense incurrence.',
                'Itemized receipts are required for all transactions exceeding the receipt threshold.',
                'Travel advances must be settled against trip claims within 14 days of return.',
                'Alcoholic beverages and personal expenses are non-reimbursable.',
            ],
        ];
    }

    /**
     * Pre-validate an expense claim prior to submission to identify potential policy blockers or warnings.
     */
    public function preValidateClaim(ExpenseClaim $claim): array
    {
        $lines = $claim->lines()->with(['category', 'receipts'])->get();
        $policy = $claim->policy;
        $version = $policy ? $this->policyService->getActiveVersionForDate($policy, $claim->claim_date ?? now()->toDateString()) : null;

        $blockers = [];
        $warnings = [];
        $recommendations = [];

        if ($lines->isEmpty()) {
            $blockers[] = 'Claim has no expense lines attached.';
        }

        $mealTotalByDate = [];
        $hotelTotalByDate = [];

        foreach ($lines as $line) {
            $date = is_string($line->expense_date) ? $line->expense_date : Carbon::parse($line->expense_date)->toDateString();
            $cat = $line->category;
            $amount = (float) $line->base_amount;

            // 1. Receipt validation
            $receiptThreshold = $cat?->receipt_threshold ?? ($version?->receipt_required_threshold ?? 0.0);
            $receiptRequired = $cat?->receipt_required ?? true;

            if ($receiptRequired && $amount > $receiptThreshold && $line->receipts->isEmpty() && ! $line->is_mileage && ! $line->is_per_diem) {
                $warnings[] = "Line #{$line->id} ({$cat?->name}) of \${$amount} requires a receipt (threshold: \${$receiptThreshold}).";
                $recommendations[] = "Attach a valid receipt or invoice for {$cat?->name} on {$date}.";
            }

            // 2. Daily meal cap simulation
            if ($cat?->category_type === 'meals' || $cat?->code === 'MEALS') {
                $mealTotalByDate[$date] = ($mealTotalByDate[$date] ?? 0.0) + $amount;
            }

            // 3. Daily hotel cap simulation
            if ($cat?->category_type === 'lodging' || $cat?->code === 'HOTEL') {
                $hotelTotalByDate[$date] = ($hotelTotalByDate[$date] ?? 0.0) + $amount;
            }
        }

        // Check meal caps
        if ($version && $version->daily_meal_limit) {
            $limit = (float) $version->daily_meal_limit;
            foreach ($mealTotalByDate as $date => $total) {
                if ($total > $limit) {
                    $excess = $total - $limit;
                    $warnings[] = "Meals on {$date} total \${$total}, which exceeds the daily cap of \${$limit} by \${$excess}.";
                    $recommendations[] = "Provide a business justification for meal expenses exceeding the daily limit on {$date}.";
                }
            }
        }

        // Check hotel caps
        if ($version && $version->daily_hotel_limit) {
            $limit = (float) $version->daily_hotel_limit;
            foreach ($hotelTotalByDate as $date => $total) {
                if ($total > $limit) {
                    $excess = $total - $limit;
                    $warnings[] = "Hotel charges on {$date} total \${$total}, which exceeds the daily cap of \${$limit} by \${$excess}.";
                    $recommendations[] = "Verify room rate compliance and attach detailed folio.";
                }
            }
        }

        // Check travel authorization linkage
        if ($claim->travel_request_id && ! $claim->travel_authorization_id) {
            $warnings[] = 'This claim is linked to a travel request that lacks an approved travel authorization.';
        }

        // Check outstanding advances
        $outstandingAdvance = (float) TravelAdvance::where('tenant_id', $claim->tenant_id)
            ->where('employee_id', $claim->employee_id)
            ->where('status', AdvanceStatus::DISBURSED->value)
            ->get()
            ->sum(fn ($adv) => $adv->remainingUnsettledAmount());

        if ($outstandingAdvance > 0) {
            $recommendations[] = "Employee has \${$outstandingAdvance} in outstanding travel advances. Settle this claim against open advances to offset reimbursement.";
        }

        $status = empty($blockers) ? (empty($warnings) ? 'compliant' : 'warnings_present') : 'blocked';
        $score = empty($blockers) ? max(50, 100 - (count($warnings) * 15)) : 20;

        return [
            'is_advisory' => true,
            'disclaimer' => self::ADVISORY_DISCLAIMER,
            'claim_id' => $claim->id,
            'claim_number' => $claim->claim_number,
            'overall_status' => $status,
            'compliance_score' => $score,
            'blockers' => $blockers,
            'warnings' => $warnings,
            'recommendations' => $recommendations,
            'can_submit' => empty($blockers),
        ];
    }

    /**
     * Provide an advisory per diem estimation with breakdown.
     */
    public function estimatePerDiem(
        string $tenantId,
        string $destinationType,
        ?string $country,
        ?string $city,
        int $days,
        bool $isDepartureDay = true,
        bool $isReturnDay = true,
        array $providedMeals = []
    ): array {
        $rateQuery = PerDiemRate::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->where('destination_type', $destinationType);

        if ($country) {
            $rateQuery->where(function ($q) use ($country) {
                $q->where('destination_country', $country)->orWhereNull('destination_country');
            });
        }

        if ($city) {
            $rateQuery->where(function ($q) use ($city) {
                $q->where('destination_city', $city)->orWhereNull('destination_city');
            });
        }

        $rate = $rateQuery->orderByDesc('destination_city')->orderByDesc('destination_country')->first();

        if (! $rate) {
            $rate = PerDiemRate::where('tenant_id', $tenantId)->where('is_active', true)->first();
        }

        $dailyRate = $rate ? (float) $rate->daily_rate : 100.0000;
        $currency = $rate ? $rate->currency : 'USD';

        $totalEstimated = 0.0;
        $dayBreakdowns = [];

        for ($day = 1; $day <= $days; $day++) {
            $isDeparture = ($day === 1 && $isDepartureDay);
            $isReturn = ($day === $days && $isReturnDay);
            $dayProvidedMeals = $providedMeals[$day] ?? [];

            $breakfastProvided = in_array('breakfast', $dayProvidedMeals);
            $lunchProvided = in_array('lunch', $dayProvidedMeals);
            $dinnerProvided = in_array('dinner', $dayProvidedMeals);

            if ($rate) {
                $calc = $this->perDiemService->calculatePerDiemAmount(
                    $rate,
                    1,
                    $isDeparture,
                    $isReturn,
                    $breakfastProvided,
                    $lunchProvided,
                    $dinnerProvided
                );
                $netDay = $calc['net_eligible_amount'];
                $grossDay = $calc['gross_amount'];
                $deductionDay = $calc['deduction_amount'];
            } else {
                $factor = ($isDeparture || $isReturn) ? 0.75 : 1.0;
                $grossDay = round($dailyRate * $factor, 4);
                $deductionDay = 0.0;
                $netDay = $grossDay;
            }

            $totalEstimated += $netDay;

            $dayBreakdowns[] = [
                'day_number' => $day,
                'is_departure' => $isDeparture,
                'is_return' => $isReturn,
                'gross_allowance' => $grossDay,
                'deductions' => $deductionDay,
                'net_allowance' => $netDay,
                'provided_meals' => $dayProvidedMeals,
            ];
        }

        return [
            'is_advisory' => true,
            'disclaimer' => self::ADVISORY_DISCLAIMER,
            'destination_type' => $destinationType,
            'country' => $country,
            'city' => $city,
            'days' => $days,
            'base_daily_rate' => $dailyRate,
            'currency' => $currency,
            'total_estimated_per_diem' => round($totalEstimated, 2),
            'days_breakdown' => $dayBreakdowns,
        ];
    }

    /**
     * Recommend settlement strategies for an employee's advances and claims.
     */
    public function recommendAdvanceSettlement(Employee $employee, ?ExpenseClaim $pendingClaim = null): array
    {
        $advances = TravelAdvance::where('tenant_id', $employee->tenant_id)
            ->where('employee_id', $employee->id)
            ->whereIn('status', [AdvanceStatus::DISBURSED->value, AdvanceStatus::PARTIALLY_DISBURSED->value])
            ->get();

        $totalOutstanding = $advances->sum(fn ($adv) => $adv->remainingUnsettledAmount());
        $claimApprovedTotal = $pendingClaim ? (float) $pendingClaim->approved_total : 0.0;

        $strategy = [];
        $netPayableToEmployee = 0.0;
        $refundDueFromEmployee = 0.0;

        if ($totalOutstanding > 0) {
            if ($claimApprovedTotal > 0) {
                if ($claimApprovedTotal >= $totalOutstanding) {
                    $netPayableToEmployee = $claimApprovedTotal - $totalOutstanding;
                    $strategy[] = "Offset full \${$totalOutstanding} outstanding advance against Claim #{$pendingClaim->claim_number}.";
                    $strategy[] = "Disburse remaining net reimbursement of \${$netPayableToEmployee} to employee.";
                } else {
                    $refundDueFromEmployee = $totalOutstanding - $claimApprovedTotal;
                    $strategy[] = "Offset \${$claimApprovedTotal} against Claim #{$pendingClaim->claim_number} (net claim becomes \$0.00).";
                    $strategy[] = "Employee owes remaining balance of \${$refundDueFromEmployee}. Recover via payroll deduction or employee bank refund.";
                }
            } else {
                $strategy[] = "Total un-reconciled advance balance is \${$totalOutstanding}. Awaiting expense claim submission.";
            }
        } else {
            $strategy[] = 'Employee has no outstanding travel advances.';
            if ($claimApprovedTotal > 0) {
                $netPayableToEmployee = $claimApprovedTotal;
                $strategy[] = "Disburse full approved claim amount of \${$claimApprovedTotal} to employee.";
            }
        }

        return [
            'is_advisory' => true,
            'disclaimer' => self::ADVISORY_DISCLAIMER,
            'employee_id' => $employee->id,
            'employee_name' => "{$employee->first_name} {$employee->last_name}",
            'total_outstanding_advances' => round($totalOutstanding, 2),
            'open_advance_count' => $advances->count(),
            'pending_claim_amount' => round($claimApprovedTotal, 2),
            'net_payable_to_employee' => round($netPayableToEmployee, 2),
            'refund_due_from_employee' => round($refundDueFromEmployee, 2),
            'recommended_steps' => $strategy,
        ];
    }

    /**
     * Refuse autonomous action attempts to enforce governance guardrails.
     */
    public function executeAutonomousAction(string $actionType): array
    {
        return [
            'success' => false,
            'is_advisory' => true,
            'action_blocked' => true,
            'reason' => 'AI is strictly advisory. Autonomous claim approvals, advance disbursements, and policy overrides are strictly prohibited by enterprise governance.',
        ];
    }

    protected function generateCategoryGuidance(ExpenseCategory $cat): string
    {
        $parts = [];
        if ($cat->max_amount) {
            $parts[] = "Capped at \${$cat->max_amount} per transaction";
        }
        if ($cat->receipt_required) {
            $threshold = (float) $cat->receipt_threshold;
            $parts[] = $threshold > 0 ? "Receipt mandatory for amounts over \${$threshold}" : 'Receipt mandatory for all amounts';
        } else {
            $parts[] = 'Receipt not required';
        }
        if (! $cat->is_reimbursable) {
            $parts[] = 'Non-reimbursable expense';
        }

        return implode('. ', $parts) . '.';
    }
}
