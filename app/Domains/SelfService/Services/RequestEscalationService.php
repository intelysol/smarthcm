<?php

namespace App\Domains\SelfService\Services;

use App\Domains\SelfService\Enums\ServiceRequestPriority;
use App\Domains\SelfService\Enums\SlaStatus;
use App\Domains\SelfService\Events\ServiceRequestSlaBreached;
use App\Domains\SelfService\Models\HrServiceEscalation;
use App\Domains\SelfService\Models\HrServiceRequest;
use App\Domains\SelfService\Models\HrServiceSlaInstance;
use App\Models\User;

class RequestEscalationService
{
    public function evaluateRequest(HrServiceRequest $request): ?HrServiceEscalation
    {
        $sla = $request->slaInstance;
        if (! $sla || in_array($sla->status, [SlaStatus::MET->value, SlaStatus::PAUSED->value]) || ! $sla->resolution_due_at) {
            return null;
        }

        $now = now();
        $due = $sla->resolution_due_at;

        if ($now->greaterThan($due)) {
            // Breached!
            if ($sla->status !== SlaStatus::BREACHED->value) {
                $sla->update(['status' => SlaStatus::BREACHED->value]);
                event(new ServiceRequestSlaBreached($request));
            }

            return $this->escalate($request, 2, 'Resolution SLA breached (>100% time consumed)');
        }

        // Check if >80% time consumed
        $totalDuration = $due->diffInMinutes($sla->created_at);
        $elapsed = $now->diffInMinutes($sla->created_at);

        if ($totalDuration > 0 && ($elapsed / $totalDuration) >= 0.8) {
            if ($sla->status !== SlaStatus::WARNING->value) {
                $sla->update(['status' => SlaStatus::WARNING->value]);
            }

            return $this->escalate($request, 1, 'Approaching SLA threshold (>80% time consumed)');
        }

        return null;
    }

    public function escalate(HrServiceRequest $request, int $level, string $reason, ?User $targetUser = null): HrServiceEscalation
    {
        // Bump priority if urgent
        if ($level >= 2 && $request->priority !== ServiceRequestPriority::CRITICAL->value) {
            $request->update(['priority' => ServiceRequestPriority::HIGH->value]);
        }

        return HrServiceEscalation::create([
            'tenant_id' => $request->tenant_id,
            'hr_service_request_id' => $request->id,
            'escalation_level' => $level,
            'reason' => $reason,
            'escalated_to_user_id' => $targetUser?->id,
            'escalated_at' => now(),
            'is_resolved' => false,
        ]);
    }
}
