<?php

namespace App\Domains\Payroll\Http\Controllers;

use App\Domains\Payroll\Models\PayrollPayslip;
use App\Domains\Payroll\Models\PayrollRun;
use App\Domains\Payroll\Services\PayrollAuthorizationService;
use App\Domains\Payroll\Services\PayslipService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PayslipController extends Controller
{
    public function __construct(
        protected PayslipService $payslipService,
        protected PayrollAuthorizationService $authService
    ) {}

    public function generate(PayrollRun $run, Request $request): JsonResponse
    {
        $payslips = $this->payslipService->generatePayslipsForRun($run, $request->user());

        return response()->json([
            'message' => "Generated {$payslips->count()} payslips.",
            'count' => $payslips->count(),
        ]);
    }

    public function publish(PayrollRun $run, Request $request): JsonResponse
    {
        $count = $this->payslipService->publishPayslipsForRun($run, $request->user());

        return response()->json([
            'message' => "Published {$count} payslips successfully.",
            'count' => $count,
        ]);
    }

    public function show(PayrollPayslip $payslip, Request $request): JsonResponse
    {
        if (! $this->authService->canViewPayslip($request->user(), $payslip)) {
            abort(403, 'Unauthorized to view this payslip.');
        }

        $payslip->load(['employee', 'run.period', 'snapshot.lines']);
        return response()->json($payslip);
    }
}
