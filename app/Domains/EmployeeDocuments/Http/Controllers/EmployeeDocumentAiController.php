<?php

namespace App\Domains\EmployeeDocuments\Http\Controllers;

use App\Domains\Employee\Models\Employee;
use App\Domains\EmployeeDocuments\Models\HcmDocumentType;
use App\Domains\EmployeeDocuments\Services\EmployeeDocumentAiService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeDocumentAiController extends Controller
{
    public function __construct(protected EmployeeDocumentAiService $aiService)
    {
    }

    public function extractMetadata(Request $request): JsonResponse
    {
        $request->validate(['filename' => 'required|string']);
        $metadata = $this->aiService->extractMetadata(
            $request->input('filename'),
            $request->input('extracted_text')
        );

        return response()->json($metadata);
    }

    public function detectDuplicate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|uuid',
            'document_type_id' => 'required|uuid',
            'document_number' => 'nullable|string',
        ]);

        $employee = Employee::findOrFail($validated['employee_id']);
        $type = HcmDocumentType::findOrFail($validated['document_type_id']);

        $result = $this->aiService->detectDuplicate($employee, $type, $validated['document_number'] ?? null);

        return response()->json($result);
    }

    public function query(Request $request): JsonResponse
    {
        $request->validate(['query' => 'required|string|max:1000']);
        $answer = $this->aiService->processAiInquiry($request->user()->tenant_id, $request->input('query'));

        return response()->json($answer);
    }
}
