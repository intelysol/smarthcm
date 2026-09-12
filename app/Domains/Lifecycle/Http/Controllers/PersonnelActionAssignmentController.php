<?php

namespace App\Domains\Lifecycle\Http\Controllers;

use App\Domains\Employee\Models\Employee;
use App\Domains\Lifecycle\Models\PersonnelTemporaryAssignment;
use App\Domains\Lifecycle\Services\PersonnelActionAssignmentService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PersonnelActionAssignmentController extends Controller
{
    public function __construct(protected PersonnelActionAssignmentService $assignmentService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;
        $assignments = PersonnelTemporaryAssignment::where('tenant_id', $tenantId)
            ->with(['employee.department', 'homeDepartment', 'temporaryDepartment'])
            ->latest()
            ->paginate(15);

        return response()->json($assignments);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|uuid',
            'assignment_type' => 'required|string|in:temporary,acting,secondment,deputation',
            'temporary_department_id' => 'nullable|uuid',
            'temporary_position_id' => 'nullable|uuid',
            'temporary_manager_id' => 'nullable|uuid',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'reason' => 'nullable|string|max:255',
        ]);

        $employee = Employee::findOrFail($validated['employee_id']);
        $assignment = $this->assignmentService->createAssignment($employee, $validated);

        return response()->json($assignment, 201);
    }

    public function revert(string $id): JsonResponse
    {
        $assignment = PersonnelTemporaryAssignment::findOrFail($id);
        $reverted = $this->assignmentService->completeAndRevertAssignment($assignment);

        return response()->json($reverted);
    }
}
