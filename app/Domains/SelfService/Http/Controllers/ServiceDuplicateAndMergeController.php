<?php

namespace App\Domains\SelfService\Http\Controllers;

use App\Domains\SelfService\Models\HrServiceRequest;
use App\Domains\SelfService\Services\ServiceDuplicateDetectionService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceDuplicateAndMergeController extends Controller
{
    public function __construct(
        protected ServiceDuplicateDetectionService $duplicateService
    ) {}

    public function findDuplicates(HrServiceRequest $request): JsonResponse
    {
        $duplicates = $this->duplicateService->findPotentialDuplicates($request);
        return response()->json($duplicates);
    }

    public function merge(HrServiceRequest $primary, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'secondary_request_id' => 'required|uuid',
            'reason' => 'sometimes|string|max:255',
        ]);

        $secondary = HrServiceRequest::findOrFail($validated['secondary_request_id']);
        $actor = $request->user();

        $result = $this->duplicateService->mergeRequests(
            $primary,
            $secondary,
            $actor,
            $validated['reason'] ?? 'Merged duplicate request'
        );

        return response()->json($result);
    }
}
