<?php

namespace App\Domains\SelfService\Http\Controllers;

use App\Domains\SelfService\Models\HrServiceRequest;
use App\Domains\SelfService\Requests\AttachRequestDocumentRequest;
use App\Domains\SelfService\Services\RequestDocumentService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class ServiceRequestDocumentController extends Controller
{
    public function __construct(
        protected RequestDocumentService $documentService
    ) {}

    public function store(AttachRequestDocumentRequest $request, HrServiceRequest $hrServiceRequest): JsonResponse
    {
        $user = $request->user();
        $doc = $this->documentService->attachDocument($hrServiceRequest, $user, $request->validated());

        return response()->json([
            'message' => 'Document attached to request successfully.',
            'data' => $doc,
        ], 201);
    }
}
