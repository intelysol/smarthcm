<?php

namespace App\Domains\Expenses\Http\Controllers;

use App\Domains\Employee\Models\Employee;
use App\Domains\Expenses\Models\ExpenseReimbursement;
use App\Domains\Expenses\Requests\CreateReimbursementRequest;
use App\Domains\Expenses\Services\ExpenseReimbursementService;
use App\Domains\Payroll\Models\PayrollPeriod;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExpenseReimbursementController extends Controller
{
    public function __construct(
        protected ExpenseReimbursementService $reimbursementService
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? 'default';

        $reimbursements = ExpenseReimbursement::query()
            ->where('tenant_id', $tenantId)
            ->with(['employee', 'lines.claim', 'payrollPeriod'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        if ($request->wantsJson()) {
            return response()->json($reimbursements);
        }

        return view('expenses.reimbursements.index', compact('reimbursements'));
    }

    public function store(CreateReimbursementRequest $request): JsonResponse
    {
        $user = $request->user();
        $employee = Employee::where('tenant_id', $user->tenant_id)->firstOrFail();
        $claims = $request->input('claim_ids');
        $method = $request->input('reimbursement_method', 'bank_payment');
        $payrollPeriod = $request->input('payroll_period_id') ? PayrollPeriod::find($request->input('payroll_period_id')) : null;

        $reimbursement = $this->reimbursementService->createReimbursement($employee, $claims, $method, $payrollPeriod);

        return response()->json([
            'message' => 'Reimbursement batch created successfully.',
            'data' => $reimbursement,
        ], 201);
    }

    public function approve(Request $request, ExpenseReimbursement $expenseReimbursement): JsonResponse
    {
        $user = $request->user();
        $approved = $this->reimbursementService->approveReimbursement($expenseReimbursement, $user);

        return response()->json([
            'message' => 'Reimbursement approved.',
            'data' => $approved,
        ]);
    }

    public function pay(Request $request, ExpenseReimbursement $expenseReimbursement): JsonResponse
    {
        $user = $request->user();
        $paid = $this->reimbursementService->markAsPaid($expenseReimbursement, $request->all(), $user);

        return response()->json([
            'message' => 'Reimbursement marked as paid.',
            'data' => $paid,
        ]);
    }
}
