<?php

namespace App\Domains\Benefits\Http\Controllers;

use App\Domains\Benefits\Models\SalaryAdvance;
use App\Domains\Benefits\Requests\StoreSalaryAdvanceRequest;
use App\Domains\Benefits\Services\SalaryAdvanceService;
use App\Domains\Employee\Models\Employee;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SalaryAdvanceController extends Controller
{
    public function __construct(
        protected SalaryAdvanceService $advanceService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;
        $advances = SalaryAdvance::where('tenant_id', $tenantId)
            ->with(['employee', 'schedules'])
            ->latest()
            ->paginate(25);

        return response()->json($advances);
    }

    public function store(StoreSalaryAdvanceRequest $request): JsonResponse
    {
        $employee = Employee::findOrFail($request->validated('employee_id'));
        $advance = $this->advanceService->requestAdvance($employee, $request->validated());

        return response()->json($advance, 201);
    }

    public function approve(Request $request, SalaryAdvance $advance): JsonResponse
    {
        $request->validate(['approved_amount' => ['required', 'numeric', 'min:0.01']]);
        $approved = $this->advanceService->approveAdvance($advance, (float) $request->input('approved_amount'), $request->user());

        return response()->json($approved);
    }
}
