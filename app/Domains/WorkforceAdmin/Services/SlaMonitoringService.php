<?php

namespace App\Domains\WorkforceAdmin\Services;

use App\Domains\WorkforceAdmin\Models\OpsSlaInstance;
use App\Domains\WorkforceAdmin\Models\OpsSlaPolicy;
use Carbon\Carbon;

class SlaMonitoringService
{
    /**
     * Start tracking SLA for a target entity.
     */
    public function startSlaTracking(string $targetType, string $targetId, string $tenantId, string $priority = 'medium'): OpsSlaInstance
    {
        $policy = OpsSlaPolicy::where('tenant_id', $tenantId)
            ->where('target_entity_type', $targetType)
            ->where('priority', $priority)
            ->where('is_active', true)
            ->first();

        $responseHours = $policy?->response_time_hours ?? 4;
        $resolutionHours = $policy?->resolution_time_hours ?? 24;

        $startedAt = now();
        $responseDue = $startedAt->copy()->addHours($responseHours);
        $resolutionDue = $startedAt->copy()->addHours($resolutionHours);

        return OpsSlaInstance::create([
            'tenant_id' => $tenantId,
            'sla_policy_id' => $policy?->id ?? (string) \Illuminate\Support\Str::uuid(),
            'target_entity_type' => $targetType,
            'target_entity_id' => $targetId,
            'started_at' => $startedAt,
            'response_due_at' => $responseDue,
            'resolution_due_at' => $resolutionDue,
            'status' => 'running',
            'is_breached' => false,
        ]);
    }

    /**
     * Record first response on an entity.
     */
    public function recordFirstResponse(string $targetType, string $targetId): ?OpsSlaInstance
    {
        $instance = OpsSlaInstance::where('target_entity_type', $targetType)
            ->where('target_entity_id', $targetId)
            ->first();

        if ($instance && !$instance->first_response_at) {
            $now = now();
            $breached = $instance->response_due_at && $now->isAfter($instance->response_due_at);
            $instance->update([
                'first_response_at' => $now,
                'is_breached' => $instance->is_breached || $breached,
            ]);
        }

        return $instance;
    }

    /**
     * Record final resolution on an entity.
     */
    public function recordResolution(string $targetType, string $targetId): ?OpsSlaInstance
    {
        $instance = OpsSlaInstance::where('target_entity_type', $targetType)
            ->where('target_entity_id', $targetId)
            ->first();

        if ($instance) {
            $now = now();
            $breached = $now->isAfter($instance->resolution_due_at);
            $instance->update([
                'resolved_at' => $now,
                'status' => $breached ? 'breached' : 'fulfilled',
                'is_breached' => $instance->is_breached || $breached,
            ]);
        }

        return $instance;
    }
}
