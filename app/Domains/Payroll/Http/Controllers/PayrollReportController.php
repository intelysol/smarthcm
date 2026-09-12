<?php

namespace App\Domains\Payroll\Http\Controllers;

use App\Domains\Payroll\Models\PayrollRun;
use App\Domains\Payroll\Services\PayrollAnalyticsService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PayrollReportController extends Controller
{
    public function __construct(protected PayrollAnalyticsService $analyticsService) {}

    public function runReport(PayrollRun $run): JsonResponse
    {
        $report = $this->analyticsService->getRunSummary($run);
        return response()->json($report);
    }
}
