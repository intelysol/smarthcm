<?php

namespace App\Domains\OrganizationDesign\Http\Controllers;

use App\Domains\OrganizationDesign\Services\AdvisoryJobAiService;
use App\Domains\OrganizationDesign\Services\ArchitectureHealthAndGovernanceService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrgDesignGovernanceController extends Controller
{
    public function __construct(
        protected ArchitectureHealthAndGovernanceService $healthService,
        protected AdvisoryJobAiService $aiService
    ) {}

    public function healthScan(Request $request): JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? (string) $request->header('X-Tenant-ID');
        $result = $this->healthService->scanHealth($tenantId);

        return response()->json($result);
    }

    public function aiDraft(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:180',
            'job_family' => 'required|string|max:150',
        ]);

        $suggestions = $this->aiService->generateProfileDraftSuggestions(
            $validated['title'],
            $validated['job_family']
        );

        return response()->json($suggestions);
    }

    public function aiStandardizeTitle(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:180',
        ]);

        $result = $this->aiService->suggestTitleStandardization($validated['title']);

        return response()->json($result);
    }
}
