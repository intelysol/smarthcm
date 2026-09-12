<?php

namespace App\Domains\Expenses\Http\Controllers;

use App\Domains\Expenses\Models\ExpensePolicy;
use App\Domains\Expenses\Requests\CreateExpensePolicyRequest;
use App\Domains\Expenses\Services\ExpensePolicyService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExpensePolicyController extends Controller
{
    public function __construct(
        protected ExpensePolicyService $policyService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? 'default';

        $policies = ExpensePolicy::query()
            ->where('tenant_id', $tenantId)
            ->with(['versions', 'assignments'])
            ->get();

        return response()->json($policies);
    }

    public function store(CreateExpensePolicyRequest $request): JsonResponse
    {
        $user = $request->user();
        $policy = $this->policyService->createPolicy(array_merge($request->validated(), [
            'tenant_id' => $user->tenant_id,
        ]));

        return response()->json([
            'message' => 'Expense policy created successfully.',
            'data' => $policy,
        ], 201);
    }
}
