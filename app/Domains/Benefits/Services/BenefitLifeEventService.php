<?php

namespace App\Domains\Benefits\Services;

use App\Domains\Benefits\Models\BenefitLifeEvent;
use App\Domains\Benefits\Models\BenefitLifeEventType;
use App\Domains\Benefits\Models\BenefitPlan;
use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Services\AuditService;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BenefitLifeEventService
{
    public function __construct(
        protected BenefitEligibilityService $eligibilityService,
        protected AuditService $auditService
    ) {}

    public function createEventType(string $tenantId, array $data, ?User $actor = null): BenefitLifeEventType
    {
        $type = BenefitLifeEventType::create([
            'tenant_id' => $tenantId,
            'code' => $data['code'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'notification_window_days' => $data['notification_window_days'] ?? 30,
            'election_window_days' => $data['election_window_days'] ?? 30,
            'documentation_deadline_days' => $data['documentation_deadline_days'] ?? 30,
            'requires_document' => (bool) ($data['requires_document'] ?? true),
            'effective_date_rule' => $data['effective_date_rule'] ?? 'event_date',
            'is_active' => $data['is_active'] ?? true,
        ]);

        $this->auditService->record(
            tenantId: $tenantId,
            eventType: 'benefit_life_event_type.created',
            action: 'create',
            entityType: BenefitLifeEventType::class,
            entityId: $type->id,
            actorId: $actor?->id,
            after: $type->toArray()
        );

        return $type;
    }

    public function reportLifeEvent(Employee $employee, array $data, ?User $actor = null): BenefitLifeEvent
    {
        $eventType = null;
        if (! empty($data['life_event_type_id'])) {
            $eventType = BenefitLifeEventType::where('tenant_id', $employee->tenant_id)->find($data['life_event_type_id']);
        } elseif (! empty($data['event_type'])) {
            $eventType = BenefitLifeEventType::where('tenant_id', $employee->tenant_id)->where('code', $data['event_type'])->first();
        }

        $eventDate = Carbon::parse($data['event_date']);
        $today = Carbon::today();

        // Check notification window deadline if configured
        $notificationWindowDays = $eventType ? $eventType->notification_window_days : 30;
        if ($today->diffInDays($eventDate, false) < -$notificationWindowDays) {
            // Reported past the allowed notification window
            // Allow submission but flag or warn according to configuration
        }

        $electionWindowDays = $eventType ? $eventType->election_window_days : 30;
        $docDeadlineDays = $eventType ? $eventType->documentation_deadline_days : 30;

        $electionWindowEnd = $today->copy()->addDays($electionWindowDays);
        $docDeadline = $today->copy()->addDays($docDeadlineDays);

        return DB::transaction(function () use ($employee, $eventType, $data, $eventDate, $electionWindowEnd, $docDeadline, $actor) {
            $lifeEvent = BenefitLifeEvent::create([
                'tenant_id' => $employee->tenant_id,
                'employee_id' => $employee->id,
                'life_event_type_id' => $eventType?->id,
                'event_type' => $eventType?->code ?? ($data['event_type'] ?? 'other'),
                'event_date' => $eventDate->toDateString(),
                'election_window_end' => $electionWindowEnd->toDateString(),
                'documentation_deadline' => $docDeadline->toDateString(),
                'documentation_status' => ! empty($data['document_id']) ? 'submitted' : 'pending',
                'affected_plans' => $data['affected_plans'] ?? null,
                'status' => 'submitted',
                'description' => $data['description'] ?? null,
                'document_id' => $data['document_id'] ?? null,
            ]);

            $this->auditService->record(
                tenantId: $employee->tenant_id,
                eventType: 'benefit_life_event.reported',
                action: 'create',
                entityType: BenefitLifeEvent::class,
                entityId: $lifeEvent->id,
                actorId: $actor?->id,
                after: $lifeEvent->toArray()
            );

            return $lifeEvent;
        });
    }

    public function verifyLifeEvent(BenefitLifeEvent $lifeEvent, User $verifier, bool $approved = true, ?string $rejectionReason = null): BenefitLifeEvent
    {
        return DB::transaction(function () use ($lifeEvent, $verifier, $approved, $rejectionReason) {
            if ($approved) {
                $lifeEvent->update([
                    'status' => 'approved',
                    'documentation_status' => 'verified',
                    'approved_by' => $verifier->id,
                    'approved_at' => now(),
                ]);

                // Recalculate eligibility for the employee across tenant plans
                $employee = $lifeEvent->employee;
                $plans = BenefitPlan::where('tenant_id', $lifeEvent->tenant_id)->where('status', 'active')->get();
                foreach ($plans as $plan) {
                    $this->eligibilityService->evaluateEligibility($employee, $plan, $lifeEvent->event_date->toDateString(), true);
                }
            } else {
                $lifeEvent->update([
                    'status' => 'rejected',
                    'documentation_status' => 'rejected',
                    'description' => trim(($lifeEvent->description ?? '') . " [Rejection Reason: {$rejectionReason}]"),
                    'approved_by' => $verifier->id,
                    'approved_at' => now(),
                ]);
            }

            $this->auditService->record(
                tenantId: $lifeEvent->tenant_id,
                eventType: $approved ? 'benefit_life_event.verified' : 'benefit_life_event.rejected',
                action: 'verify',
                entityType: BenefitLifeEvent::class,
                entityId: $lifeEvent->id,
                actorId: $verifier->id,
                after: $lifeEvent->toArray()
            );

            return $lifeEvent;
        });
    }

    public function getEventTypes(string $tenantId): Collection
    {
        return BenefitLifeEventType::where('tenant_id', $tenantId)->where('is_active', true)->get();
    }

    public function getLifeEventsForEmployee(Employee $employee): Collection
    {
        return BenefitLifeEvent::where('tenant_id', $employee->tenant_id)
            ->where('employee_id', $employee->id)
            ->with(['lifeEventType', 'approver'])
            ->orderByDesc('event_date')
            ->get();
    }
}
