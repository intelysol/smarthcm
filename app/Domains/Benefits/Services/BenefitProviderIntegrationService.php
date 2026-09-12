<?php

namespace App\Domains\Benefits\Services;

use App\Domains\Benefits\Models\BenefitEnrollment;
use App\Domains\Benefits\Models\BenefitIntegration;
use App\Domains\Benefits\Models\BenefitPlan;
use App\Domains\Benefits\Models\BenefitProvider;
use App\Domains\Benefits\Models\BenefitProviderMapping;
use App\Domains\Integration\Services\IntegrationService;
use App\Domains\Shared\Services\AuditService;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class BenefitProviderIntegrationService
{
    public function __construct(
        protected IntegrationService $integrationService,
        protected AuditService $auditService
    ) {}

    public function createOrUpdateMapping(
        BenefitProvider $provider,
        BenefitPlan $plan,
        array $data,
        ?User $actor = null
    ): BenefitProviderMapping {
        $mapping = BenefitProviderMapping::updateOrCreate(
            [
                'tenant_id' => $provider->tenant_id,
                'benefit_provider_id' => $provider->id,
                'benefit_plan_id' => $plan->id,
            ],
            [
                'external_plan_code' => $data['external_plan_code'],
                'external_plan_name' => $data['external_plan_name'] ?? $plan->name,
                'policy_number' => $data['policy_number'] ?? $provider->policy_number,
                'group_number' => $data['group_number'] ?? null,
                'effective_from' => $data['effective_from'] ?? now()->toDateString(),
                'effective_to' => $data['effective_to'] ?? null,
                'sync_status' => 'synced',
                'mapping_metadata' => $data['mapping_metadata'] ?? [],
            ]
        );

        $this->auditService->record(
            tenantId: $provider->tenant_id,
            eventType: 'benefit_provider_mapping.saved',
            action: 'save',
            entityType: BenefitProviderMapping::class,
            entityId: $mapping->id,
            actorId: $actor?->id,
            after: $mapping->toArray()
        );

        return $mapping;
    }

    /**
     * Export active plan enrollments for a provider
     */
    public function exportEnrollmentsToProvider(BenefitProvider $provider, ?User $actor = null): BenefitIntegration
    {
        $tenantId = $provider->tenant_id;
        $mappings = BenefitProviderMapping::where('tenant_id', $tenantId)
            ->where('benefit_provider_id', $provider->id)
            ->with('plan')
            ->get();

        $planIds = $mappings->pluck('benefit_plan_id')->toArray();

        $enrollments = BenefitEnrollment::query()
            ->where('tenant_id', $tenantId)
            ->whereIn('benefit_plan_id', $planIds)
            ->whereIn('status', ['approved', 'active'])
            ->with(['employee', 'plan', 'dependents'])
            ->get();

        $payload = [
            'provider_code' => $provider->provider_code,
            'provider_name' => $provider->name,
            'policy_number' => $provider->policy_number,
            'exported_at' => now()->toIso8601String(),
            'total_enrollments' => $enrollments->count(),
            'records' => $enrollments->map(function ($enr) use ($mappings) {
                $mapping = $mappings->firstWhere('benefit_plan_id', $enr->benefit_plan_id);
                return [
                    'enrollment_id' => $enr->id,
                    'employee_number' => $enr->employee?->employee_number,
                    'employee_name' => "{$enr->employee?->first_name} {$enr->employee?->last_name}",
                    'national_id' => $enr->employee?->national_id,
                    'date_of_birth' => $enr->employee?->date_of_birth?->toDateString(),
                    'gender' => $enr->employee?->gender,
                    'internal_plan_code' => $enr->plan?->code,
                    'external_plan_code' => $mapping?->external_plan_code ?? $enr->plan?->code,
                    'group_number' => $mapping?->group_number,
                    'coverage_level' => $enr->coverage_level,
                    'effective_from' => $enr->effective_from?->toDateString(),
                    'effective_to' => $enr->effective_to?->toDateString(),
                    'status' => $enr->status,
                    'dependents' => $enr->dependents->map(fn ($d) => [
                        'name' => $d->name,
                        'relationship' => $d->relationship,
                        'date_of_birth' => $d->date_of_birth?->toDateString(),
                        'national_id' => $d->national_id,
                    ])->toArray(),
                ];
            })->toArray(),
        ];

        return DB::transaction(function () use ($provider, $payload, $actor, $mappings) {
            $integration = BenefitIntegration::create([
                'tenant_id' => $provider->tenant_id,
                'integration_type' => 'provider',
                'target_name' => $provider->name,
                'reference_type' => BenefitProvider::class,
                'reference_id' => $provider->id,
                'direction' => 'export',
                'status' => 'sent',
                'payload' => $payload,
                'processed_at' => now(),
            ]);

            // Update mappings last exported time
            foreach ($mappings as $m) {
                $m->update(['last_exported_at' => now(), 'sync_status' => 'synced']);
            }

            // Publish to Integration Hub
            $this->integrationService->publish(
                tenantId: $provider->tenant_id,
                type: 'benefits.provider.exported',
                payload: $payload,
                subjectType: BenefitIntegration::class,
                subjectId: $integration->id
            );

            $this->auditService->record(
                tenantId: $provider->tenant_id,
                eventType: 'benefit_provider.exported',
                action: 'export',
                entityType: BenefitIntegration::class,
                entityId: $integration->id,
                actorId: $actor?->id,
                after: ['integration_id' => $integration->id, 'enrollments_count' => count($payload['records'])]
            );

            return $integration;
        });
    }

    public function updateIntegrationStatus(BenefitIntegration $integration, string $status, ?array $responsePayload = null, ?string $error = null): BenefitIntegration
    {
        $integration->update([
            'status' => $status,
            'response_payload' => $responsePayload,
            'error_message' => $error,
            'processed_at' => now(),
        ]);

        return $integration;
    }

    public function getProviderMappings(string $tenantId, ?string $providerId = null): Collection
    {
        $query = BenefitProviderMapping::where('tenant_id', $tenantId)->with(['provider', 'plan']);
        if ($providerId) {
            $query->where('benefit_provider_id', $providerId);
        }
        return $query->get();
    }
}
