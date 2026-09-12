<?php

namespace App\Domains\Recruitment\Http\Controllers;

use App\Domains\Recruitment\Models\HcmRecruitmentJobPosting;
use App\Domains\Recruitment\Services\ApplicationService;
use App\Domains\Recruitment\Services\CandidateService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicCareersController extends Controller
{
    public function __construct(
        protected CandidateService $candidateService,
        protected ApplicationService $applicationService
    ) {
    }

    public function careersWeb(Request $request): View
    {
        $postings = HcmRecruitmentJobPosting::where('status', 'published')
            ->with('requisition')
            ->latest()
            ->paginate(12);

        return view('recruitment.public.careers', compact('postings'));
    }

    public function jobDetailWeb(string $slug): View
    {
        $posting = HcmRecruitmentJobPosting::where('slug', $slug)
            ->where('status', 'published')
            ->with(['requisition.department', 'requisition.template'])
            ->firstOrFail();

        return view('recruitment.public.job_detail', compact('posting'));
    }

    public function applyPublic(Request $request, string $postingId): JsonResponse
    {
        $posting = HcmRecruitmentJobPosting::where('id', $postingId)
            ->where('status', 'published')
            ->firstOrFail();

        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:150',
            'phone' => 'nullable|string|max:50',
            'cover_letter' => 'nullable|string',
            'resume_filename' => 'nullable|string',
            'resume_path' => 'nullable|string',
        ]);

        // Find or create candidate
        $candidate = $this->candidateService->createCandidate([
            'tenant_id' => $posting->tenant_id,
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'resume_path' => $validated['resume_path'] ?? null,
            'resume_filename' => $validated['resume_filename'] ?? null,
        ]);

        $application = $this->applicationService->apply($candidate, $posting->requisition, [
            'cover_letter' => $validated['cover_letter'] ?? null,
        ]);

        return response()->json([
            'message' => 'Your application has been received successfully.',
            'application_number' => $application->application_number,
        ], 201);
    }
}
