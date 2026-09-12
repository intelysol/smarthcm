<?php

namespace App\Domains\OrganizationDesign\Http\Controllers;

use App\Domains\OrganizationDesign\Models\JobProfile;
use App\Domains\OrganizationDesign\Services\ArchitectureImpactAnalysisService;
use App\Domains\OrganizationDesign\Services\JobProfileService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobProfileController extends Controller
{
    public function __construct(
        protected JobProfileService $profileService,
        protected ArchitectureImpactAnalysisService $impactService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? (string) $request->header('X-Tenant-ID');
        $profiles = JobProfile::where('tenant_id', $tenantId)
            ->with(['family', 'subFamily', 'careerTrack', 'careerLevel', 'jobLevel'])
            ->latest()
            ->paginate($request->input('per_page', 25));

        return response()->json($profiles);
    }

    public function store(Request $request): JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? (string) $request->header('X-Tenant-ID');
        $validated = $request->validate([
            'job_id' => 'nullable|uuid',
            'job_family_id' => 'required|uuid',
            'job_sub_family_id' => 'nullable|uuid',
            'career_track_id' => 'nullable|uuid',
            'career_level_id' => 'nullable|uuid',
            'job_level_id' => 'nullable|uuid',
            'job_grade_id' => 'nullable|uuid',
            'code' => 'required|string|max:80',
            'title' => 'required|string|max:180',
            'summary' => 'nullable|string',
            'responsibilities' => 'nullable|array',
            'requirements' => 'nullable|array',
            'education_requirement' => 'nullable|string',
            'experience_years_min' => 'nullable|numeric',
            'certifications' => 'nullable|array',
            'travel_requirement' => 'nullable|string',
            'remote_eligibility' => 'nullable|string|in:onsite,hybrid,remote',
            'status' => 'nullable|string|in:draft,review,approved,published,retired',
        ]);

        $profile = $this->profileService->createProfile($tenantId, $validated, $request->user());

        return response()->json($profile, 201);
    }

    public function show(string $id): JsonResponse
    {
        $profile = JobProfile::with(['family', 'subFamily', 'careerTrack', 'careerLevel', 'jobLevel', 'skills.skill', 'competencies.competency', 'versions'])
            ->findOrFail($id);

        return response()->json($profile);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $profile = JobProfile::findOrFail($id);
        $validated = $request->validate([
            'title' => 'sometimes|string|max:180',
            'summary' => 'nullable|string',
            'responsibilities' => 'nullable|array',
            'requirements' => 'nullable|array',
            'education_requirement' => 'nullable|string',
            'experience_years_min' => 'nullable|numeric',
            'change_summary' => 'nullable|string',
        ]);

        $changeSummary = $validated['change_summary'] ?? 'Updated profile configuration';
        unset($validated['change_summary']);

        $updated = $this->profileService->updateProfile($profile, $validated, $request->user(), $changeSummary);

        return response()->json($updated);
    }

    public function impact(string $id): JsonResponse
    {
        $profile = JobProfile::findOrFail($id);
        $impact = $this->impactService->assessJobProfileImpact($profile);

        return response()->json($impact);
    }
}
