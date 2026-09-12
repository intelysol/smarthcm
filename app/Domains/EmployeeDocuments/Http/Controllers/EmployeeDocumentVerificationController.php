<?php

namespace App\Domains\EmployeeDocuments\Http\Controllers;

use App\Domains\EmployeeDocuments\Models\EmployeeDocument;
use App\Domains\EmployeeDocuments\Services\EmployeeDocumentVerificationService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeDocumentVerificationController extends Controller
{
    public function __construct(protected EmployeeDocumentVerificationService $verificationService)
    {
    }

    public function verify(Request $request, string $id): JsonResponse
    {
        $doc = EmployeeDocument::with(['sharedDocument', 'requirement'])->findOrFail($id);
        $verified = $this->verificationService->verify($doc, $request->user(), $request->input('comments'));

        return response()->json($verified);
    }

    public function reject(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'reason' => 'required|string|max:255',
            'comments' => 'nullable|string',
        ]);

        $doc = EmployeeDocument::with(['sharedDocument', 'requirement'])->findOrFail($id);
        $rejected = $this->verificationService->reject(
            $doc,
            $request->user(),
            $request->input('reason'),
            $request->input('comments')
        );

        return response()->json($rejected);
    }
}
