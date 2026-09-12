<?php

namespace App\Domains\Benefits\Http\Controllers;

use App\Domains\Benefits\Services\BenefitPayrollReconciliationService;
use App\Domains\Payroll\Models\PayrollPeriod;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BenefitPayrollReconciliationController extends Controller
{
    public function __construct(
        protected BenefitPayrollReconciliationService $reconciliationService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? (string) $request->header('X-Tenant-ID');
        $periodId = $request->query('payroll_period_id');
        $status = $request->query('status');

        $reconciliations = $this->reconciliationService->getReconciliations($tenantId, $periodId, $status);

        return response()->json([
            'success' => true,
            'data' => $reconciliations,
        ]);
    }

    public function reconcile(PayrollPeriod $period, Request $request): JsonResponse
    {
        $result = $this->reconciliationService->reconcilePeriod($period, $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Benefits and payroll deduction reconciliation completed.',
            'data' => $result,
        ]);
    }
}
