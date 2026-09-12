<?php

namespace App\Domains\OrganizationDesign\Http\Controllers;

use App\Domains\OrganizationDesign\Models\JobFamily;
use App\Domains\OrganizationDesign\Services\JobArchitectureService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobArchitectureController extends Controller
{
    public function __construct(
        protected JobArchitectureService $architectureService
    ) {}

    public function tree(Request $request): JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? (string) $request->header('X-Tenant-ID');
        $tree = $this->architectureService->getArchitectureTree($tenantId);

        return response()->json($tree);
    }

    public function storeFamily(Request $request): JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? (string) $request->header('X-Tenant-ID');
        $validated = $request->validate([
            'code' => 'required|string|max:80',
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
        ]);

        $family = $this->architectureService->createFamily($tenantId, $validated);

        return response()->json($family, 201);
    }

    public function storeSubFamily(Request $request, string $familyId): JsonResponse
    {
        $family = JobFamily::findOrFail($familyId);
        $validated = $request->validate([
            'code' => 'required|string|max:80',
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
        ]);

        $subFamily = $this->architectureService->createSubFamily($family, $validated);

        return response()->json($subFamily, 201);
    }

    public function storeCareerTrack(Request $request): JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? (string) $request->header('X-Tenant-ID');
        $validated = $request->validate([
            'code' => 'required|string|max:80',
            'name' => 'required|string|max:150',
            'track_type' => 'nullable|string|in:individual_contributor,management,technical_specialist,executive',
            'description' => 'nullable|string',
        ]);

        $track = $this->architectureService->createCareerTrack($tenantId, $validated);

        return response()->json($track, 201);
    }

    public function storeJobLevel(Request $request): JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? (string) $request->header('X-Tenant-ID');
        $validated = $request->validate([
            'code' => 'required|string|max:80',
            'name' => 'required|string|max:150',
            'numerical_level' => 'required|integer',
            'description' => 'nullable|string',
        ]);

        $level = $this->architectureService->createJobLevel($tenantId, $validated);

        return response()->json($level, 201);
    }
}
