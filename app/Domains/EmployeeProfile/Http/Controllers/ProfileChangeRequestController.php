<?php

namespace App\Domains\EmployeeProfile\Http\Controllers;

use App\Domains\Employee\Models\Employee;
use App\Domains\EmployeeProfile\Models\EmployeeProfileChangeRequest;
use App\Domains\EmployeeProfile\Services\ProfileChangeRequestService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileChangeRequestController extends Controller
{
    public function __construct(protected ProfileChangeRequestService $changeService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;
        $status = $request->input('status', 'pending');

        $requests = EmployeeProfileChangeRequest::where('tenant_id', $tenantId)
            ->where('status', $status)
            ->with(['employee.department', 'employee.designation', 'items'])
            ->latest('requested_at')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $requests->items(),
            'pagination' => [
                'current_page' => $requests->currentPage(),
                'last_page' => $requests->lastPage(),
                'total' => $requests->total(),
            ],
        ]);
    }

    public function store(Request $request, string $employeeId): JsonResponse
    {
        $employee = Employee::findOrFail($employeeId);
        $validated = $request->validate([
            'changes' => 'required|array|min:1',
            'comments' => 'nullable|string|max:500',
        ]);

        $changeRequest = $this->changeService->createChangeRequest(
            $employee,
            $validated['changes'],
            $validated['comments'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => 'Profile change request submitted for HR review.',
            'data' => $changeRequest,
        ], 201);
    }

    public function approve(Request $request, string $id): JsonResponse
    {
        $changeRequest = EmployeeProfileChangeRequest::findOrFail($id);
        $comments = $request->input('comments');

        $approved = $this->changeService->approveRequest($changeRequest, $request->user(), $comments);

        return response()->json([
            'success' => true,
            'message' => 'Profile change request approved and applied to Core HR record.',
            'data' => $approved,
        ]);
    }

    public function reject(Request $request, string $id): JsonResponse
    {
        $changeRequest = EmployeeProfileChangeRequest::findOrFail($id);
        $validated = $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        $rejected = $this->changeService->rejectRequest($changeRequest, $request->user(), $validated['reason']);

        return response()->json([
            'success' => true,
            'message' => 'Profile change request rejected.',
            'data' => $rejected,
        ]);
    }
}
