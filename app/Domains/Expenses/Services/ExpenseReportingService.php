<?php

namespace App\Domains\Expenses\Services;

use App\Domains\Expenses\Models\ExpenseClaim;
use App\Domains\Expenses\Models\ExpenseClaimLine;
use App\Domains\Expenses\Models\ExpensePolicyResult;
use App\Domains\Expenses\Models\ExpenseReimbursement;
use App\Domains\Expenses\Models\TravelAdvance;
use App\Domains\Expenses\Models\TravelRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ExpenseReportingService
{
    public function getDashboardSummary(string $tenantId): array
    {
        $totalClaims = ExpenseClaim::where('tenant_id', $tenantId)->count();
        $approvedClaimsAmount = (float) ExpenseClaim::where('tenant_id', $tenantId)->whereIn('status', ['approved', 'ready_for_payment', 'paid'])->sum('approved_total');
        $pendingApprovalAmount = (float) ExpenseClaim::where('tenant_id', $tenantId)->whereIn('status', ['submitted', 'under_review', 'finance_review'])->sum('claimed_total');

        $activeTravelCount = TravelRequest::where('tenant_id', $tenantId)->whereIn('status', ['approved', 'completed'])->count();
        $totalTravelCost = (float) TravelRequest::where('tenant_id', $tenantId)->whereIn('status', ['approved', 'completed'])->sum('estimated_cost');

        $outstandingAdvances = (float) TravelAdvance::where('tenant_id', $tenantId)->whereIn('status', ['disbursed', 'partially_disbursed', 'overdue'])->sum(DB::raw('disbursed_amount - settled_amount'));

        $totalReimbursed = (float) ExpenseReimbursement::where('tenant_id', $tenantId)->where('status', 'paid')->sum('total_reimbursement_amount');
        $policyViolationsCount = ExpensePolicyResult::where('tenant_id', $tenantId)->where('is_violation', true)->count();

        return [
            'total_claims' => $totalClaims,
            'approved_claims_amount' => $approvedClaimsAmount,
            'pending_approval_amount' => $pendingApprovalAmount,
            'active_travel_count' => $activeTravelCount,
            'total_travel_cost' => $totalTravelCost,
            'outstanding_advances' => $outstandingAdvances,
            'total_reimbursed' => $totalReimbursed,
            'policy_violations_count' => $policyViolationsCount,
        ];
    }

    public function getDepartmentExpenseBreakdown(string $tenantId, ?string $startDate = null, ?string $endDate = null): array
    {
        $query = ExpenseClaimLine::query()
            ->where('expense_claim_lines.tenant_id', $tenantId)
            ->whereNotNull('department_id')
            ->join('departments', 'departments.id', '=', 'expense_claim_lines.department_id')
            ->selectRaw('departments.department_name, departments.department_code, SUM(expense_claim_lines.approved_base_amount) as total_expense')
            ->groupBy('departments.department_name', 'departments.department_code');

        if ($startDate && $endDate) {
            $query->whereBetween('expense_claim_lines.expense_date', [$startDate, $endDate]);
        }

        return $query->get()->toArray();
    }

    public function getCategoryExpenseBreakdown(string $tenantId): array
    {
        return ExpenseClaimLine::query()
            ->where('expense_claim_lines.tenant_id', $tenantId)
            ->join('expense_categories', 'expense_categories.id', '=', 'expense_claim_lines.expense_category_id')
            ->selectRaw('expense_categories.name as category_name, expense_categories.code as category_code, SUM(expense_claim_lines.approved_base_amount) as total_amount')
            ->groupBy('expense_categories.name', 'expense_categories.code')
            ->get()
            ->toArray();
    }
}
