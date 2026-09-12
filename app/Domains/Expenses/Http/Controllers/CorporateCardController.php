<?php

namespace App\Domains\Expenses\Http\Controllers;

use App\Domains\Employee\Models\Employee;
use App\Domains\Expenses\Models\CorporateCard;
use App\Domains\Expenses\Models\CorporateCardTransaction;
use App\Domains\Expenses\Models\ExpenseClaimLine;
use App\Domains\Expenses\Services\CorporateCardService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CorporateCardController extends Controller
{
    public function __construct(
        protected CorporateCardService $cardService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? 'default';

        $cards = CorporateCard::query()
            ->where('tenant_id', $tenantId)
            ->with(['employee', 'transactions'])
            ->get();

        return response()->json($cards);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        $employee = Employee::where('tenant_id', $user->tenant_id)->firstOrFail();

        $card = $this->cardService->assignCard($employee, $request->all());

        return response()->json([
            'message' => 'Corporate card assigned successfully.',
            'data' => $card,
        ], 201);
    }

    public function match(Request $request, CorporateCardTransaction $transaction): JsonResponse
    {
        $user = $request->user();
        $claimLine = ExpenseClaimLine::findOrFail($request->input('expense_claim_line_id'));

        $match = $this->cardService->matchTransactionToClaimLine($transaction, $claimLine, $user);

        return response()->json([
            'message' => 'Transaction matched to expense claim line.',
            'data' => $match,
        ]);
    }
}
