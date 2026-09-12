<?php

namespace App\Domains\Expenses\Http\Controllers;

use App\Domains\Employee\Models\Employee;
use App\Domains\Expenses\Models\TravelAdvance;
use App\Domains\Expenses\Requests\CreateTravelAdvanceRequest;
use App\Domains\Expenses\Services\TravelAdvanceService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TravelAdvanceController extends Controller
{
    public function __construct(
        protected TravelAdvanceService $advanceService
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? 'default';

        $advances = TravelAdvance::query()
            ->where('tenant_id', $tenantId)
            ->with(['employee', 'travelRequest', 'disbursements', 'settlements'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        if ($request->wantsJson()) {
            return response()->json($advances);
        }

        return view('expenses.advances.index', compact('advances'));
    }

    public function store(CreateTravelAdvanceRequest $request): JsonResponse
    {
        $user = $request->user();
        $employee = Employee::where('tenant_id', $user->tenant_id)->firstOrFail();

        $advance = $this->advanceService->requestAdvance($employee, $request->validated());

        return response()->json([
            'message' => 'Travel advance requested.',
            'data' => $advance,
        ], 201);
    }

    public function approve(Request $request, TravelAdvance $travelAdvance): JsonResponse
    {
        $user = $request->user();
        $approvedAmount = (float) ($request->input('approved_amount') ?? $travelAdvance->requested_amount);

        $approved = $this->advanceService->approveAdvance($travelAdvance, $approvedAmount, $user);

        return response()->json([
            'message' => 'Travel advance approved.',
            'data' => $approved,
        ]);
    }

    public function disburse(Request $request, TravelAdvance $travelAdvance): JsonResponse
    {
        $user = $request->user();
        $disbursement = $this->advanceService->disburseAdvance($travelAdvance, $request->all(), $user);

        return response()->json([
            'message' => 'Travel advance disbursed.',
            'data' => $disbursement,
        ]);
    }
}
