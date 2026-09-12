<?php

namespace App\Domains\Expenses\Http\Controllers;

use App\Domains\Expenses\Models\ExpenseCategory;
use App\Domains\Expenses\Models\ExpensePolicy;
use App\Domains\Expenses\Services\ExpensePolicyService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExpensePolicyWebController extends Controller
{
    public function __construct(
        protected ExpensePolicyService $policyService
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? 'default';

        $policies = ExpensePolicy::where('tenant_id', $tenantId)
            ->with(['versions' => fn ($q) => $q->orderByDesc('version_number'), 'assignments'])
            ->get();

        $categories = ExpenseCategory::where('tenant_id', $tenantId)
            ->orderBy('name')
            ->get();

        if ($request->wantsJson()) {
            return response()->json([
                'policies' => $policies,
                'categories' => $categories,
            ]);
        }

        return view('expenses.policies.index', compact('policies', 'categories'));
    }
}
