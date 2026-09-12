<?php

namespace App\Domains\EmployeeDocuments\Http\Controllers;

use App\Domains\Employee\Models\Employee;
use App\Domains\EmployeeDocuments\Models\EmployeeDocumentRequirement;
use App\Domains\EmployeeDocuments\Models\HcmDocumentType;
use App\Domains\EmployeeDocuments\Services\EmployeeDocumentRequirementService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeDocumentRequirementController extends Controller
{
    public function __construct(protected EmployeeDocumentRequirementService $reqService)
    {
    }

    public function index(Request $request, string $employeeId): JsonResponse
    {
        $employee = Employee::findOrFail($employeeId);
        $completeness = $this->reqService->evaluateCompleteness($employee);

        return response()->json($completeness);
    }

    public function assign(Request $request, string $employeeId): JsonResponse
    {
        $validated = $request->validate([
            'document_type_id' => 'required|uuid',
            'is_mandatory' => 'nullable|boolean',
            'due_date' => 'nullable|date',
        ]);

        $employee = Employee::findOrFail($employeeId);
        $docType = HcmDocumentType::findOrFail($validated['document_type_id']);

        $req = $this->reqService->assignRequirement(
            $employee,
            $docType,
            $validated['is_mandatory'] ?? true,
            $validated['due_date'] ?? null
        );

        return response()->json($req, 201);
    }

    public function waive(Request $request, string $id): JsonResponse
    {
        $request->validate(['reason' => 'required|string|max:255']);
        $req = EmployeeDocumentRequirement::with('documentType')->findOrFail($id);

        $waived = $this->reqService->waiveRequirement($req, $request->user(), $request->input('reason'));

        return response()->json($waived);
    }
}
