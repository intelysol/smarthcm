<?php

namespace App\Domains\Payroll\Http\Controllers;

use App\Domains\Payroll\Models\PayrollRun;
use App\Domains\Payroll\Services\PayrollAnalyticsService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class PayrollReviewController extends Controller
{
    public function __construct(protected PayrollAnalyticsService $analyticsService) {}

    public function review(PayrollRun $run): JsonResponse
    {
        $run->load([
            'period',
            'calculationSnapshots.employee.department',
            'calculationSnapshots.employee.designation',
            'exceptions',
            'variances.employee',
        ]);

        $summary = $this->analyticsService->getRunSummary($run);

        return response()->json([
            'run' => $run,
            'kpis' => $summary,
        ]);
    }
}
