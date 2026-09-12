<?php

namespace App\Domains\Expenses\Http\Controllers;

use App\Domains\Employee\Models\Employee;
use App\Domains\Expenses\Models\ExpenseClaim;
use App\Domains\Expenses\Models\ExpenseClaimLine;
use App\Domains\Expenses\Requests\AddExpenseClaimLineRequest;
use App\Domains\Expenses\Requests\CreateExpenseClaimRequest;
use App\Domains\Expenses\Services\ExpenseClaimService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExpenseClaimController extends Controller
{
    public function __construct(
        protected ExpenseClaimService $claimService
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? 'default';

        $claims = ExpenseClaim::query()
            ->where('tenant_id', $tenantId)
            ->with(['employee', 'travelAuthorization', 'lines.category'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        if ($request->wantsJson()) {
            return response()->json($claims);
        }

        return view('expenses.claims.index', compact('claims'));
    }

    public function show(ExpenseClaim $claim): View|JsonResponse
    {
        $claim->load(['employee', 'travelAuthorization.travelRequest', 'lines.category', 'lines.receipts', 'lines.policyResults', 'exceptions']);

        if (request()->wantsJson()) {
            return response()->json($claim);
        }

        return view('expenses.claims.show', compact('claim'));
    }

    public function store(CreateExpenseClaimRequest $request): JsonResponse
    {
        $user = $request->user();
        $employee = Employee::where('tenant_id', $user->tenant_id)->firstOrFail();

        $claim = $this->claimService->createClaim($employee, $request->validated());

        return response()->json([
            'message' => 'Expense claim created successfully.',
            'data' => $claim,
        ], 201);
    }

    public function addLine(AddExpenseClaimLineRequest $request, ExpenseClaim $claim): JsonResponse
    {
        $line = $this->claimService->addLine($claim, $request->validated());

        return response()->json([
            'message' => 'Expense line added successfully.',
            'data' => $line,
        ], 201);
    }

    public function submit(ExpenseClaim $claim): JsonResponse
    {
        $submitted = $this->claimService->submitClaim($claim);

        return response()->json([
            'message' => 'Expense claim submitted for approval.',
            'data' => $submitted,
        ]);
    }

    public function approve(Request $request, ExpenseClaim $claim): JsonResponse
    {
        $user = $request->user();
        $approved = $this->claimService->approveByManager($claim, $user);

        return response()->json([
            'message' => 'Expense claim approved by manager.',
            'data' => $approved,
        ]);
    }

    public function financeApprove(Request $request, ExpenseClaim $claim): JsonResponse
    {
        $user = $request->user();
        $approved = $this->claimService->approveByFinance($claim, $user);

        return response()->json([
            'message' => 'Expense claim authorized by finance.',
            'data' => $approved,
        ]);
    }

    public function overridePolicy(Request $request, ExpenseClaimLine $line): JsonResponse
    {
        $user = $request->user();
        $reason = $request->input('reason', 'Approved exception by management');

        $updated = $this->claimService->overridePolicyViolation($line, $user, $reason);

        return response()->json([
            'message' => 'Policy violation overridden successfully.',
            'data' => $updated,
        ]);
    }
}
