<?php

namespace App\Domains\Recruitment\Http\Controllers;

use App\Domains\Recruitment\Models\HcmRecruitmentCandidate;
use App\Domains\Recruitment\Services\CandidateService;
use App\Domains\Recruitment\Services\RecruitmentSecurityService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CandidateController extends Controller
{
    public function __construct(
        protected CandidateService $candidateService,
        protected RecruitmentSecurityService $securityService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;
        $query = HcmRecruitmentCandidate::where('tenant_id', $tenantId)
            ->with(['profile', 'tags', 'source']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return response()->json($query->latest()->paginate(15));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:150',
            'phone' => 'nullable|string|max:50',
            'location' => 'nullable|string|max:100',
            'source_id' => 'nullable|uuid',
            'headline' => 'nullable|string|max:200',
            'summary' => 'nullable|string',
            'skills' => 'nullable|array',
            'tags' => 'nullable|array',
        ]);

        $validated['tenant_id'] = $request->user()->tenant_id;
        $candidate = $this->candidateService->createCandidate($validated);

        return response()->json($candidate, 201);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $candidate = HcmRecruitmentCandidate::where('id', $id)
            ->with(['profile', 'tags', 'source', 'applications.requisition'])
            ->firstOrFail();

        $this->securityService->authorizeCandidateAccess($request->user(), $candidate);

        return response()->json($candidate);
    }

    public function checkDuplicates(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email']);
        $duplicates = $this->candidateService->detectDuplicates(
            $request->user()->tenant_id,
            $request->input('email'),
            $request->input('phone')
        );

        return response()->json([
            'count' => $duplicates->count(),
            'duplicates' => $duplicates,
        ]);
    }
}
