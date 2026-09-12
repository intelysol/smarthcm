<?php

namespace App\Domains\Expenses\Http\Controllers;

use App\Domains\Expenses\Models\CorporateCard;
use App\Domains\Expenses\Models\CorporateCardTransaction;
use App\Domains\Expenses\Services\CorporateCardService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CorporateCardWebController extends Controller
{
    public function __construct(
        protected CorporateCardService $cardService
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? 'default';

        $cards = CorporateCard::where('tenant_id', $tenantId)
            ->with(['employee'])
            ->get();

        $transactions = CorporateCardTransaction::where('tenant_id', $tenantId)
            ->with(['employee', 'card', 'matchedClaimLine'])
            ->orderByDesc('transaction_date')
            ->limit(50)
            ->get();

        $matchedCount = $transactions->where('is_matched', true)->count();
        $unmatchedCount = $transactions->where('is_matched', false)->count();

        if ($request->wantsJson()) {
            return response()->json([
                'cards' => $cards,
                'transactions' => $transactions,
                'metrics' => [
                    'total_transactions' => $transactions->count(),
                    'matched_count' => $matchedCount,
                    'unmatched_count' => $unmatchedCount,
                ],
            ]);
        }

        return view('expenses.cards.index', compact('cards', 'transactions', 'matchedCount', 'unmatchedCount'));
    }
}
