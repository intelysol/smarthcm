<?php

namespace App\Domains\Expenses\Http\Controllers;

use App\Domains\Employee\Models\Employee;
use App\Domains\Expenses\Models\TravelRequest;
use App\Domains\Expenses\Requests\CreateTravelRequestRequest;
use App\Domains\Expenses\Services\TravelRequestService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TravelRequestController extends Controller
{
    public function __construct(
        protected TravelRequestService $travelRequestService
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? 'default';

        $requests = TravelRequest::query()
            ->where('tenant_id', $tenantId)
            ->with(['employee', 'authorization', 'segments'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        if ($request->wantsJson()) {
            return response()->json($requests);
        }

        return view('expenses.travel.index', compact('requests'));
    }

    public function store(CreateTravelRequestRequest $request): JsonResponse
    {
        $user = $request->user();
        $employee = Employee::where('tenant_id', $user->tenant_id)->firstOrFail();

        $travel = $this->travelRequestService->createTravelRequest($employee, $request->validated());

        return response()->json([
            'message' => 'Travel request created successfully.',
            'data' => $travel,
        ], 201);
    }

    public function submit(TravelRequest $travelRequest): JsonResponse
    {
        $updated = $this->travelRequestService->submitTravelRequest($travelRequest);

        return response()->json([
            'message' => 'Travel request submitted for approval.',
            'data' => $updated,
        ]);
    }

    public function approve(Request $request, TravelRequest $travelRequest): JsonResponse
    {
        $user = $request->user();
        $budget = $request->input('approved_budget');

        $approved = $this->travelRequestService->approveTravelRequest($travelRequest, $user, $budget ? (float)$budget : null);

        return response()->json([
            'message' => 'Travel request authorized.',
            'data' => $approved,
        ]);
    }

    public function reject(Request $request, TravelRequest $travelRequest): JsonResponse
    {
        $user = $request->user();
        $reason = $request->input('reason');

        $rejected = $this->travelRequestService->rejectTravelRequest($travelRequest, $user, $reason);

        return response()->json([
            'message' => 'Travel request rejected.',
            'data' => $rejected,
        ]);
    }
}
