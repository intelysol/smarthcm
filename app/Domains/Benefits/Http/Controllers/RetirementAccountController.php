<?php

namespace App\Domains\Benefits\Http\Controllers;

use App\Domains\Benefits\Models\RetirementAccount;
use App\Domains\Benefits\Requests\StoreRetirementWithdrawalRequest;
use App\Domains\Benefits\Services\RetirementAccountService;
use App\Domains\Benefits\Services\RetirementWithdrawalService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RetirementAccountController extends Controller
{
    public function __construct(
        protected RetirementAccountService $accountService,
        protected RetirementWithdrawalService $withdrawalService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;
        $accounts = RetirementAccount::where('tenant_id', $tenantId)
            ->with(['employee', 'plan'])
            ->paginate(25);

        return response()->json($accounts);
    }

    public function show(RetirementAccount $account): JsonResponse
    {
        $statement = $this->accountService->getAccountStatement($account);
        return response()->json([
            'account' => $account->load(['employee', 'plan', 'transactions']),
            'statement' => $statement,
        ]);
    }

    public function withdraw(StoreRetirementWithdrawalRequest $request): JsonResponse
    {
        $account = RetirementAccount::findOrFail($request->validated('retirement_account_id'));
        $withdrawal = $this->withdrawalService->requestWithdrawal($account, $request->validated());

        return response()->json($withdrawal, 201);
    }
}
