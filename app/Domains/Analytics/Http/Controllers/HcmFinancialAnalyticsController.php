<?php

namespace App\Domains\Analytics\Http\Controllers;

use App\Domains\Analytics\Services\HcmAnalyticsSecurityService;
use App\Domains\Analytics\Services\HcmCompensationAndPayrollAnalyticsService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HcmFinancialAnalyticsController extends Controller
{
    public function __construct(
        protected HcmCompensationAndPayrollAnalyticsService $payrollService,
        protected HcmAnalyticsSecurityService $securityService
    ) {}

    public function payroll(Request $request): View|JsonResponse
    {
        $user = $request->user();
        if ($user && ! $this->securityService->canViewPayrollAnalytics($user)) {
            abort(403, 'Unauthorized. Access to confidential payroll and compensation analytics requires hcm.analytics.payroll.view permission.');
        }

        $tenantId = $user?->tenant_id ?? 'default';
        $summary = $this->payrollService->getPayrollSummary($tenantId);
        $distribution = $this->payrollService->getSalaryDistribution($tenantId);
        $expenses = $this->payrollService->getExpenseAndBenefitsSummary($tenantId);

        if ($request->wantsJson()) {
            return response()->json([
                'payroll_summary' => $summary,
                'salary_distribution' => $distribution,
                'expenses_and_benefits' => $expenses,
            ]);
        }

        return view('analytics.payroll', compact('summary', 'distribution', 'expenses'));
    }
}
