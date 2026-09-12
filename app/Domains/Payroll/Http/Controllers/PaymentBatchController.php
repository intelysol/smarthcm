<?php

namespace App\Domains\Payroll\Http\Controllers;

use App\Domains\Payroll\Models\PayrollPaymentBatch;
use App\Domains\Payroll\Models\PayrollRun;
use App\Domains\Payroll\Requests\PaymentBatchRequest;
use App\Domains\Payroll\Services\PaymentBatchService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PaymentBatchController extends Controller
{
    public function __construct(protected PaymentBatchService $paymentService) {}

    public function store(PaymentBatchRequest $request): JsonResponse
    {
        $run = PayrollRun::query()->findOrFail($request->input('payroll_run_id'));
        $batch = $this->paymentService->createPaymentBatch($run, $request->validated(), $request->user());

        return response()->json([
            'message' => 'Payment batch created successfully.',
            'data' => $batch,
        ], 201);
    }

    public function export(PayrollPaymentBatch $batch): Response
    {
        $csv = $this->paymentService->generateExportFile($batch);

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"payment-batch-{$batch->batch_number}.csv\"",
        ]);
    }

    public function submit(PayrollPaymentBatch $batch, Request $request): JsonResponse
    {
        $batch = $this->paymentService->submitPaymentBatch($batch, $request->user());

        return response()->json([
            'message' => 'Payment batch submitted.',
            'data' => $batch,
        ]);
    }

    public function markPaid(PayrollPaymentBatch $batch, Request $request): JsonResponse
    {
        $batch = $this->paymentService->markAsPaid($batch, $request->user());

        return response()->json([
            'message' => 'Payment batch marked as paid.',
            'data' => $batch,
        ]);
    }
}
