<?php

namespace App\Domains\Payroll\Http\Controllers;

use App\Domains\Payroll\Models\PayrollPeriod;
use App\Domains\Payroll\Requests\PayrollPeriodRequest;
use App\Domains\Payroll\Services\PayrollPeriodService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PayrollPeriodController extends Controller
{
    public function __construct(protected PayrollPeriodService $periodService) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;
        $periods = PayrollPeriod::query()
            ->where('tenant_id', $tenantId)
            ->with(['calendar', 'legalEntity'])
            ->orderByDesc('start_date')
            ->paginate($request->integer('per_page', 25));

        return response()->json($periods);
    }

    public function store(PayrollPeriodRequest $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;
        $period = $this->periodService->createPeriod($tenantId, $request->validated(), $request->user());

        return response()->json([
            'message' => 'Payroll period created successfully.',
            'data' => $period,
        ], 201);
    }

    public function show(PayrollPeriod $period): JsonResponse
    {
        $period->load(['calendar', 'legalEntity', 'runs.payslips', 'lockLogs.actor']);
        return response()->json($period);
    }

    public function lock(Request $request, PayrollPeriod $period): JsonResponse
    {
        $reason = $request->string('reason')->toString();
        $this->periodService->lockPeriod($period, $request->user(), $reason);

        return response()->json([
            'message' => "Payroll period {$period->period_name} locked successfully.",
            'data' => $period->fresh(),
        ]);
    }

    public function reopen(Request $request, PayrollPeriod $period): JsonResponse
    {
        $request->validate(['reason' => 'required|string|min:5']);
        $this->periodService->reopenPeriod($period, $request->user(), $request->input('reason'));

        return response()->json([
            'message' => "Payroll period {$period->period_name} reopened successfully.",
            'data' => $period->fresh(),
        ]);
    }
}
