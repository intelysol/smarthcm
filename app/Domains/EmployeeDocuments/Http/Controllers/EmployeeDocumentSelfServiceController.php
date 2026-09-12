<?php

namespace App\Domains\EmployeeDocuments\Http\Controllers;

use App\Domains\Employee\Models\Employee;
use App\Domains\EmployeeDocuments\Models\EmployeeDocument;
use App\Domains\EmployeeDocuments\Models\EmployeeDocumentRequest;
use App\Domains\EmployeeDocuments\Models\EmployeeDocumentRequirement;
use App\Domains\EmployeeDocuments\Models\HcmDocumentType;
use App\Domains\EmployeeDocuments\Services\EmployeeDocumentAcknowledgementService;
use App\Domains\EmployeeDocuments\Services\EmployeeDocumentRequirementService;
use App\Domains\EmployeeDocuments\Services\EmployeeDocumentService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class EmployeeDocumentSelfServiceController extends Controller
{
    public function __construct(
        protected EmployeeDocumentService $docService,
        protected EmployeeDocumentRequirementService $reqService,
        protected EmployeeDocumentAcknowledgementService $ackService
    ) {
    }

    public function portalView(Request $request): View
    {
        $employeeId = $request->user()->employee_id;
        $employee = $employeeId ? Employee::find($employeeId) : null;

        $documents = $employee
            ? EmployeeDocument::where('employee_id', $employeeId)->where('employee_visible', true)->with(['documentType.category', 'sharedDocument'])->get()
            : collect();

        $requirements = $employee
            ? EmployeeDocumentRequirement::where('employee_id', $employeeId)->with(['documentType.category', 'employeeDocument'])->get()
            : collect();

        $requests = $employee
            ? EmployeeDocumentRequest::where('employee_id', $employeeId)->where('status', 'requested')->with('documentType')->get()
            : collect();

        return view('employee_documents.employee.portal', compact('employee', 'documents', 'requirements', 'requests'));
    }

    public function myDocuments(Request $request): JsonResponse
    {
        $employeeId = $request->user()->employee_id;
        if (!$employeeId) {
            throw ValidationException::withMessages(['employee' => 'User is not linked to an employee profile.']);
        }

        $docs = EmployeeDocument::where('employee_id', $employeeId)
            ->where('employee_visible', true)
            ->with(['documentType.category', 'sharedDocument.versions'])
            ->latest()
            ->get();

        return response()->json($docs);
    }

    public function myRequirements(Request $request): JsonResponse
    {
        $employeeId = $request->user()->employee_id;
        if (!$employeeId) {
            throw ValidationException::withMessages(['employee' => 'User is not linked to an employee profile.']);
        }

        $employee = Employee::findOrFail($employeeId);
        $completeness = $this->reqService->evaluateCompleteness($employee);

        return response()->json($completeness);
    }

    public function myRequests(Request $request): JsonResponse
    {
        $employeeId = $request->user()->employee_id;
        if (!$employeeId) {
            throw ValidationException::withMessages(['employee' => 'User is not linked to an employee profile.']);
        }

        $requests = EmployeeDocumentRequest::where('employee_id', $employeeId)
            ->with(['documentType', 'requester'])
            ->latest()
            ->get();

        return response()->json($requests);
    }

    public function acknowledge(Request $request, string $id): JsonResponse
    {
        $employeeId = $request->user()->employee_id;
        if (!$employeeId) {
            throw ValidationException::withMessages(['employee' => 'User is not linked to an employee profile.']);
        }

        $employee = Employee::findOrFail($employeeId);
        $doc = EmployeeDocument::where('id', $id)
            ->where('employee_id', $employeeId)
            ->firstOrFail();

        $ack = $this->ackService->acknowledge(
            $doc,
            $employee,
            $request->ip(),
            $request->userAgent()
        );

        return response()->json($ack, 201);
    }
}
