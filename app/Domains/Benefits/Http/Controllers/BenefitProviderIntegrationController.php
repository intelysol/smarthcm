<?php

namespace App\Domains\Benefits\Http\Controllers;

use App\Domains\Benefits\Models\BenefitPlan;
use App\Domains\Benefits\Models\BenefitProvider;
use App\Domains\Benefits\Services\BenefitProviderIntegrationService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BenefitProviderIntegrationController extends Controller
{
    public function __construct(
        protected BenefitProviderIntegrationService $providerIntegrationService
    ) {}

    public function mappings(Request $request): JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? (string) $request->header('X-Tenant-ID');
        $providerId = $request->query('provider_id');
        $mappings = $this->providerIntegrationService->getProviderMappings($tenantId, $providerId);

        return response()->json([
            'success' => true,
            'data' => $mappings,
        ]);
    }

    public function storeMapping(BenefitProvider $provider, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'benefit_plan_id' => 'required|uuid|exists:benefit_plans,id',
            'external_plan_code' => 'required|string|max:100',
            'external_plan_name' => 'nullable|string|max:150',
            'policy_number' => 'nullable|string|max:100',
            'group_number' => 'nullable|string|max:100',
            'effective_from' => 'nullable|date',
            'effective_to' => 'nullable|date',
            'mapping_metadata' => 'nullable|array',
        ]);

        $plan = BenefitPlan::findOrFail($validated['benefit_plan_id']);
        $mapping = $this->providerIntegrationService->createOrUpdateMapping($provider, $plan, $validated, $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Provider plan mapping saved.',
            'data' => $mapping->load(['provider', 'plan']),
        ], 201);
    }

    public function export(BenefitProvider $provider, Request $request): JsonResponse
    {
        $integration = $this->providerIntegrationService->exportEnrollmentsToProvider($provider, $request->user());

        return response()->json([
            'success' => true,
            'message' => "Enrollment data dispatched for provider '{$provider->name}'.",
            'data' => $integration,
        ]);
    }
}
