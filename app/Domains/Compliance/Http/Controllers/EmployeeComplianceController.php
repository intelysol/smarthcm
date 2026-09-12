<?php

namespace App\Domains\Compliance\Http\Controllers;

use App\Domains\Compliance\Services\ComplianceEvaluationService;
use App\Domains\Compliance\Services\EmployeeComplianceService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class EmployeeComplianceController extends Controller
{
    public function __construct(
        protected EmployeeComplianceService $complianceService,
        protected ComplianceEvaluationService $evaluationService
    ) {}

    /**
     * List compliance obligations for an employee.
     */
    public function index(string $employeeId): JsonResponse
    {
        $profile = $this->complianceService->getComplianceProfile($employeeId);

        return response()->json([
            'success' => true,
            'data' => $profile['requirements'] ?? [],
        ]);
    }

    /**
     * Assign compliance requirement to an employee.
     */
    public function store(\Illuminate\Http\Request $request, string $employeeId): JsonResponse
    {
        $validated = $request->validate([
            'compliance_requirement_id' => 'required|uuid',
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $employee = \App\Domains\Employee\Models\Employee::findOrFail($employeeId);

        $assignment = \App\Domains\Compliance\Models\HcmEmployeeComplianceRequirement::create([
            'tenant_id' => $employee->tenant_id,
            'employee_id' => $employeeId,
            'requirement_id' => $validated['compliance_requirement_id'],
            'due_date' => $validated['due_date'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'status' => 'required',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Compliance requirement assigned to employee.',
            'data' => $assignment,
        ], 201);
    }

    /**
     * Get compliance profile bundle for an employee.
     */
    public function profile(string $employeeId): JsonResponse
    {
        $profile = $this->complianceService->getComplianceProfile($employeeId);

        return response()->json([
            'success' => true,
            'data' => $profile,
        ]);
    }

    /**
     * Summary of employee compliance.
     */
    public function summary(string $employeeId): JsonResponse
    {
        $profile = $this->complianceService->getComplianceProfile($employeeId);

        return response()->json([
            'success' => true,
            'data' => $profile['snapshot'] ?? null,
        ]);
    }

    /**
     * Force evaluate compliance obligations for an employee.
     */
    public function evaluate(string $employeeId): JsonResponse
    {
        $snapshot = $this->evaluationService->evaluateEmployee($employeeId);

        return response()->json([
            'success' => true,
            'message' => 'Employee compliance evaluated successfully.',
            'data' => $snapshot,
        ]);
    }
}
