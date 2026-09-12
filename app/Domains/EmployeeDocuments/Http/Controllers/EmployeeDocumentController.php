<?php

namespace App\Domains\EmployeeDocuments\Http\Controllers;

use App\Domains\Employee\Models\Employee;
use App\Domains\EmployeeDocuments\Models\EmployeeDocument;
use App\Domains\EmployeeDocuments\Models\HcmDocumentType;
use App\Domains\EmployeeDocuments\Services\EmployeeDocumentSecurityService;
use App\Domains\EmployeeDocuments\Services\EmployeeDocumentService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeDocumentController extends Controller
{
    public function __construct(
        protected EmployeeDocumentService $docService,
        protected EmployeeDocumentSecurityService $securityService
    ) {
    }

    public function index(Request $request, string $employeeId): JsonResponse
    {
        $employee = Employee::findOrFail($employeeId);
        $personnelFile = $this->docService->getPersonnelFile($employee, $request->user());

        return response()->json($personnelFile);
    }

    public function store(Request $request, string $employeeId): JsonResponse
    {
        $validated = $request->validate([
            'document_type_id' => 'required|uuid',
            'title' => 'nullable|string|max:200',
            'document_number' => 'nullable|string|max:100',
            'issue_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
            'file' => 'nullable|file|max:20480', // 20MB
            'metadata' => 'nullable|array',
        ]);

        $employee = Employee::findOrFail($employeeId);
        $docType = HcmDocumentType::findOrFail($validated['document_type_id']);

        $doc = $this->docService->storeDocument(
            $request->user(),
            $employee,
            $docType,
            $request->file('file'),
            $validated
        );

        return response()->json($doc, 201);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $doc = EmployeeDocument::with(['documentType.category', 'sharedDocument.versions', 'verifications.reviewer', 'acknowledgements'])
            ->findOrFail($id);

        $this->securityService->authorizeAccess($request->user(), $doc, 'view');

        return response()->json($doc);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:200',
            'document_number' => 'nullable|string|max:100',
            'issue_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
            'metadata' => 'nullable|array',
        ]);

        $doc = EmployeeDocument::findOrFail($id);
        $this->securityService->authorizeAccess($request->user(), $doc, 'update');

        $doc->update($validated);

        return response()->json($doc);
    }

    public function replace(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'file' => 'nullable|file|max:20480',
            'document_number' => 'nullable|string|max:100',
            'issue_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
            'change_notes' => 'nullable|string|max:255',
            'metadata' => 'nullable|array',
        ]);

        $doc = EmployeeDocument::with('sharedDocument')->findOrFail($id);
        $this->securityService->authorizeAccess($request->user(), $doc, 'update');

        $replaced = $this->docService->replaceDocument(
            $doc,
            $request->user(),
            $request->file('file'),
            $validated
        );

        return response()->json($replaced);
    }

    public function download(Request $request, string $id): JsonResponse
    {
        $doc = EmployeeDocument::with('sharedDocument.versions')->findOrFail($id);
        $this->securityService->authorizeAccess($request->user(), $doc, 'download');

        $downloadUrl = $this->securityService->getSecureDownloadUrl($doc);

        return response()->json([
            'download_url' => $downloadUrl,
            'expires_in' => 3600,
        ]);
    }
}
