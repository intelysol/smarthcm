<?php

namespace App\Domains\Onboarding\Http\Controllers;

use App\Domains\Onboarding\Models\HcmOnboardingDocumentRequirement;
use App\Domains\Onboarding\Services\OnboardingDocumentService;
use App\Domains\Onboarding\Services\OnboardingSecurityService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OnboardingDocumentController extends Controller
{
    public function __construct(
        protected OnboardingDocumentService $documentService,
        protected OnboardingSecurityService $securityService
    ) {
    }

    public function submit(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'file_path' => 'required|string|max:255',
            'file_name' => 'required|string|max:150',
        ]);

        $req = HcmOnboardingDocumentRequirement::with('case')->findOrFail($id);
        $this->securityService->authorizeCaseAccess($request->user(), $req->case);

        $submitted = $this->documentService->submitDocument(
            $req,
            $validated['file_path'],
            $validated['file_name']
        );

        return response()->json($submitted);
    }

    public function verify(Request $request, string $id): JsonResponse
    {
        $req = HcmOnboardingDocumentRequirement::with('case')->findOrFail($id);
        $this->securityService->authorizeCaseAccess($request->user(), $req->case);

        $verified = $this->documentService->verifyDocument(
            $req,
            $request->user()->id,
            $request->input('comments')
        );

        return response()->json($verified);
    }

    public function reject(Request $request, string $id): JsonResponse
    {
        $request->validate(['reason' => 'required|string|max:255']);
        $req = HcmOnboardingDocumentRequirement::with('case')->findOrFail($id);
        $this->securityService->authorizeCaseAccess($request->user(), $req->case);

        $rejected = $this->documentService->rejectDocument(
            $req,
            $request->user()->id,
            $request->input('reason')
        );

        return response()->json($rejected);
    }
}
