<?php

namespace App\Domains\Expenses\Http\Controllers;

use App\Domains\Expenses\Models\ExpenseAccountingExport;
use App\Domains\Expenses\Models\ExpensePeriodLock;
use App\Domains\Expenses\Services\ExpenseAccountingService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExpenseAccountingWebController extends Controller
{
    public function __construct(
        protected ExpenseAccountingService $accountingService
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? 'default';

        $exports = ExpenseAccountingExport::where('tenant_id', $tenantId)
            ->with(['exportedBy'])
            ->orderByDesc('created_at')
            ->get();

        $periodLocks = ExpensePeriodLock::where('tenant_id', $tenantId)
            ->with(['lockedBy'])
            ->orderByDesc('created_at')
            ->get();

        if ($request->wantsJson()) {
            return response()->json([
                'exports' => $exports,
                'period_locks' => $periodLocks,
            ]);
        }

        return view('expenses.accounting.index', compact('exports', 'periodLocks'));
    }

    public function export(Request $request): RedirectResponse|JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? 'default';
        $user = $request->user() ?? \App\Models\User::first();

        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $export = $this->accountingService->generateAccountingExport(
            $tenantId,
            $validated['start_date'],
            $validated['end_date'],
            $user
        );

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Accounting export generated successfully.',
                'export' => $export,
            ], 201);
        }

        return redirect()->route('expenses.accounting.index')->with('success', "GL export batch {$export->batch_number} created.");
    }

    public function lockPeriod(Request $request): RedirectResponse|JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? 'default';
        $user = $request->user() ?? \App\Models\User::first();

        $validated = $request->validate([
            'period_name' => 'required|string|max:100',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $lock = $this->accountingService->lockPeriod(
            $tenantId,
            $validated['period_name'],
            $validated['start_date'],
            $validated['end_date'],
            $user
        );

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Accounting period locked successfully.',
                'lock' => $lock,
            ], 201);
        }

        return redirect()->route('expenses.accounting.index')->with('success', "Period '{$lock->period_name}' locked.");
    }
}
