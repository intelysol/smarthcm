<?php

namespace App\Domains\Payroll\Http\Controllers;

use App\Domains\Payroll\Models\PayrollRun;
use App\Domains\Payroll\Services\PayrollAccountingService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PayrollAccountingController extends Controller
{
    public function __construct(protected PayrollAccountingService $accountingService) {}

    public function export(PayrollRun $run, Request $request): JsonResponse
    {
        $export = $this->accountingService->generateAccountingExport($run, $request->user());

        return response()->json([
            'message' => 'Accounting journal voucher generated successfully.',
            'data' => $export,
        ]);
    }
}
