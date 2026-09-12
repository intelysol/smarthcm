<?php

namespace App\Domains\EmployeeDocuments\Http\Controllers;

use App\Domains\Employee\Models\Employee;
use App\Domains\EmployeeDocuments\Models\EmployeeDocumentRequest;
use App\Domains\EmployeeDocuments\Models\HcmDocumentType;
use App\Domains\EmployeeDocuments\Services\EmployeeDocumentRequestService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeDocumentRequestController extends Controller
{
    public function __construct(protected EmployeeDocumentRequestService $requestService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $query = EmployeeDocumentRequest::where('tenant_id', $request->user()->tenant_id)
            ->with(['employee', 'documentType', 'requester']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        return response()->json($query->latest()->paginate(15));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|uuid',
            'document_type_id' => 'required|uuid',
            'due_date' => 'nullable|date',
            'instructions' => 'nullable|string',
        ]);

        $employee = Employee::findOrFail($validated['employee_id']);
        $docType = HcmDocumentType::findOrFail($validated['document_type_id']);

        $req = $this->requestService->createRequest(
            $request->user(),
            $employee,
            $docType,
            $validated['due_date'] ?? null,
            $validated['instructions'] ?? null
        );

        return response()->json($req, 201);
    }

    public function cancel(Request $request, string $id): JsonResponse
    {
        $request->validate(['reason' => 'required|string|max:255']);
        $req = EmployeeDocumentRequest::findOrFail($id);

        $cancelled = $this->requestService->cancelRequest($req, $request->user(), $request->input('reason'));

        return response()->json($cancelled);
    }
}
