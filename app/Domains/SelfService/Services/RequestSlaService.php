<?php

namespace App\Domains\SelfService\Services;

use App\Domains\SelfService\Enums\SlaStatus;
use App\Domains\SelfService\Events\ServiceRequestSlaBreached;
use App\Domains\SelfService\Models\HrServiceRequest;
use App\Domains\SelfService\Models\HrServiceSlaInstance;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RequestSlaService
{
    public function initializeSla(HrServiceRequest $request): ?HrServiceSlaInstance
    {
        $policy = $request->service?->slaPolicy;
        if (! $policy) {
            return null;
        }

        $now = now();
        $responseMinutes = $policy->response_time_minutes ?? 240;
        $resolutionMinutes = $policy->resolution_time_minutes ?? 2880;

        $responseDue = $now->copy()->addMinutes($responseMinutes);
        $resolutionDue = $now->copy()->addMinutes($resolutionMinutes);

        $instance = HrServiceSlaInstance::create([
            'tenant_id' => $request->tenant_id,
            'hr_service_request_id' => $request->id,
            'hr_service_sla_policy_id' => $policy->id,
            'response_due_at' => $responseDue,
            'resolution_due_at' => $resolutionDue,
            'status' => SlaStatus::RUNNING->value,
            'total_paused_minutes' => 0,
        ]);

        $instance->events()->create([
            'tenant_id' => $request->tenant_id,
            'event_type' => 'started',
            'event_at' => $now,
            'details' => "SLA started under policy {$policy->name}",
        ]);

        $request->update([
            'sla_instance_id' => $instance->id,
            'due_at' => $resolutionDue,
        ]);

        return $instance;
    }

    public function recordFirstResponse(HrServiceRequest $request): void
    {
        $sla = $request->slaInstance;
        if ($sla && ! $sla->responded_at) {
            $now = now();
            $sla->update(['responded_at' => $now]);
            $request->update(['first_response_at' => $now]);

            $sla->events()->create([
                'tenant_id' => $request->tenant_id,
                'event_type' => 'first_response',
                'event_at' => $now,
                'details' => 'Agent responded to ticket',
            ]);
        }
    }

    public function pauseSla(HrServiceRequest $request, string $reason): void
    {
        $sla = $request->slaInstance;
        if ($sla && $sla->status === SlaStatus::RUNNING->value) {
            $now = now();
            $sla->update([
                'status' => SlaStatus::PAUSED->value,
                'last_paused_at' => $now,
            ]);

            $sla->events()->create([
                'tenant_id' => $request->tenant_id,
                'event_type' => 'paused',
                'event_at' => $now,
                'details' => $reason,
            ]);
        }
    }

    public function resumeSla(HrServiceRequest $request): void
    {
        $sla = $request->slaInstance;
        if ($sla && $sla->status === SlaStatus::PAUSED->value && $sla->last_paused_at) {
            $now = now();
            $pausedMinutes = abs((int) $now->diffInMinutes($sla->last_paused_at));

            $newTotalPaused = (int) $sla->total_paused_minutes + $pausedMinutes;
            $newResolutionDue = $sla->resolution_due_at ? Carbon::parse($sla->resolution_due_at)->addMinutes($pausedMinutes) : null;

            $sla->update([
                'status' => SlaStatus::RUNNING->value,
                'total_paused_minutes' => $newTotalPaused,
                'resolution_due_at' => $newResolutionDue,
                'last_paused_at' => null,
            ]);

            $request->update(['due_at' => $newResolutionDue]);

            $sla->events()->create([
                'tenant_id' => $request->tenant_id,
                'event_type' => 'resumed',
                'event_at' => $now,
                'details' => "Resumed after {$pausedMinutes} minutes pause. Due date extended.",
            ]);
        }
    }

    public function markResolutionMet(HrServiceRequest $request): void
    {
        $sla = $request->slaInstance;
        if ($sla && ! $sla->resolved_at) {
            $now = now();
            $status = ($sla->resolution_due_at && $now->greaterThan($sla->resolution_due_at))
                ? SlaStatus::BREACHED->value
                : SlaStatus::MET->value;

            $sla->update([
                'resolved_at' => $now,
                'status' => $status,
            ]);

            $sla->events()->create([
                'tenant_id' => $request->tenant_id,
                'event_type' => $status,
                'event_at' => $now,
                'details' => "Ticket marked as {$status}",
            ]);

            if ($status === SlaStatus::BREACHED->value) {
                event(new ServiceRequestSlaBreached($request));
            }
        }
    }
}
