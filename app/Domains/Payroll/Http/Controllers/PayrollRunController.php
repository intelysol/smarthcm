<?php

namespace App\Domains\Payroll\Http\Controllers;

use App\Domains\Payroll\Models\PayrollPeriod;
use App\Domains\Payroll\Models\PayrollRun;
use App\Domains\Payroll\Requests\PayrollRunRequest;
use App\Domains\Payroll\Services\PayrollRunService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PayrollRunController extends Controller
{
    public function __construct(protected PayrollRunService $runService) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;
        $runs = PayrollRun::query()
            ->where('tenant_id', $tenantId)
            ->with(['period', 'legalEntity'])
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 25));

        return response()->json($runs);
    }

    public function store(PayrollRunRequest $request): JsonResponse
    {
        $period = PayrollPeriod::query()->findOrFail($request->input('payroll_period_id'));
        $run = $this->runService->createRun($period, $request->validated(), $request->user());

        return response()->json([
            'message' => 'Payroll run created successfully.',
            'data' => $run,
        ], 201);
    }

    public function show(PayrollRun $run): JsonResponse
    {
        $run->load(['period', 'legalEntity', 'exceptions', 'variances', 'paymentBatches', 'accountingExports']);
        return response()->json($run);
    }

    public function calculate(PayrollRun $run, Request $request): JsonResponse
    {
        $run = $this->runService->calculateRun($run, $request->user());

        return response()->json([
            'message' => 'Payroll calculation completed.',
            'data' => $run,
        ]);
    }

    public function submit(PayrollRun $run, Request $request): JsonResponse
    {
        $run = $this->runService->submitForReview($run, $request->user());

        return response()->json([
            'message' => 'Payroll run submitted for review.',
            'data' => $run,
        ]);
    }

    public function approve(PayrollRun $run, Request $request): JsonResponse
    {
        $run = $this->runService->approveRun($run, $request->user());

        return response()->json([
            'message' => 'Payroll run approved successfully.',
            'data' => $run,
        ]);
    }

    public function lock(PayrollRun $run, Request $request): JsonResponse
    {
        $run = $this->runService->lockRun($run, $request->user());

        return response()->json([
            'message' => 'Payroll run locked successfully.',
            'data' => $run,
        ]);
    }
}
