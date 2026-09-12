<?php

namespace App\Domains\Benefits\Http\Controllers;

use App\Domains\Benefits\Models\LoanApplication;
use App\Domains\Benefits\Models\LoanProduct;
use App\Domains\Benefits\Requests\StoreLoanApplicationRequest;
use App\Domains\Benefits\Requests\StoreLoanRestructureRequest;
use App\Domains\Benefits\Services\LoanApplicationService;
use App\Domains\Benefits\Services\LoanDisbursementService;
use App\Domains\Benefits\Services\LoanRestructureService;
use App\Domains\Benefits\Services\LoanSettlementService;
use App\Domains\Employee\Models\Employee;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoanApplicationController extends Controller
{
    public function __construct(
        protected LoanApplicationService $applicationService,
        protected LoanDisbursementService $disbursementService,
        protected LoanRestructureService $restructureService,
        protected LoanSettlementService $settlementService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;
        $loans = LoanApplication::where('tenant_id', $tenantId)
            ->with(['employee', 'product', 'agreement', 'activeSchedule'])
            ->latest()
            ->paginate(25);

        return response()->json($loans);
    }

    public function store(StoreLoanApplicationRequest $request): JsonResponse
    {
        $employee = Employee::findOrFail($request->validated('employee_id'));
        $product = LoanProduct::findOrFail($request->validated('loan_product_id'));

        $application = $this->applicationService->apply($employee, $product, $request->validated());
        return response()->json($application, 201);
    }

    public function show(LoanApplication $loan): JsonResponse
    {
        return response()->json($loan->load(['employee', 'product', 'agreement', 'activeSchedule.installments', 'transactions', 'restructures', 'settlements']));
    }

    public function approve(Request $request, LoanApplication $loan): JsonResponse
    {
        $approved = $this->applicationService->approve($loan, $request->all(), $request->user());
        return response()->json($approved);
    }

    public function disburse(Request $request, LoanApplication $loan): JsonResponse
    {
        $disbursement = $this->disbursementService->disburse($loan, $request->all(), $request->user());
        return response()->json($disbursement);
    }

    public function restructure(StoreLoanRestructureRequest $request, LoanApplication $loan): JsonResponse
    {
        $restructure = $this->restructureService->restructure(
            $loan,
            (int) $request->validated('new_tenure_months'),
            (float) $request->validated('new_interest_rate'),
            $request->validated('reason'),
            $request->user()
        );

        return response()->json($restructure);
    }

    public function settle(Request $request, LoanApplication $loan): JsonResponse
    {
        $settlement = $this->settlementService->earlySettlement(
            $loan,
            (float) ($request->input('rebate_amount') ?? 0),
            $request->input('payment_method') ?? 'payroll_deduction',
            $request->user()
        );

        return response()->json($settlement);
    }
}
