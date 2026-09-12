<?php

namespace App\Domains\Benefits\Services;

use App\Domains\Benefits\Models\BenefitElection;
use App\Domains\Benefits\Models\BenefitEnrollmentWindow;
use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Services\AuditService;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BenefitOpenEnrollmentService
{
    public function __construct(
        protected BenefitElectionService $electionService,
        protected AuditService $auditService
    ) {}

    public function createWindow(string $tenantId, array $data, ?User $creator = null): BenefitEnrollmentWindow
    {
        $window = BenefitEnrollmentWindow::create([
            'tenant_id' => $tenantId,
            'name' => $data['name'],
            'plan_year' => $data['plan_year'],
            'start_date' => $data['start_date'],
            'close_date' => $data['close_date'],
            'effective_date' => $data['effective_date'],
            'status' => $data['status'] ?? 'draft',
            'allow_late_enrollment' => (bool) ($data['allow_late_enrollment'] ?? false),
            'description' => $data['description'] ?? null,
            'created_by' => $creator?->id,
            'updated_by' => $creator?->id,
        ]);

        $this->auditService->record(
            tenantId: $tenantId,
            eventType: 'benefit_open_enrollment.created',
            action: 'create',
            entityType: BenefitEnrollmentWindow::class,
            entityId: $window->id,
            actorId: $creator?->id,
            after: $window->toArray()
        );

        return $window;
    }

    public function openWindow(BenefitEnrollmentWindow $window, ?User $actor = null): BenefitEnrollmentWindow
    {
        $window->update(['status' => 'open']);

        $this->auditService->record(
            tenantId: $window->tenant_id,
            eventType: 'benefit_open_enrollment.opened',
            action: 'open',
            entityType: BenefitEnrollmentWindow::class,
            entityId: $window->id,
            actorId: $actor?->id,
            after: $window->toArray()
        );

        return $window;
    }

    public function getProgress(BenefitEnrollmentWindow $window): array
    {
        $tenantId = $window->tenant_id;
        $activeEmployees = Employee::where('tenant_id', $tenantId)->where('employment_status', 'active')->count();

        $electionsGrouped = BenefitElection::where('benefit_enrollment_window_id', $window->id)
            ->select('employee_id', 'status', 'is_waived')
            ->get()
            ->groupBy('employee_id');

        $completedCount = 0;
        $inProgressCount = 0;
        $waivedCount = 0;

        foreach ($electionsGrouped as $empElections) {
            $hasConfirmedOrApproved = $empElections->contains(fn ($e) => in_array($e->status, ['confirmed', 'approved'], true));
            $allWaived = $empElections->every(fn ($e) => (bool) $e->is_waived);

            if ($allWaived) {
                $waivedCount++;
            } elseif ($hasConfirmedOrApproved) {
                $completedCount++;
            } else {
                $inProgressCount++;
            }
        }

        $participatingCount = $electionsGrouped->count();
        $notStartedCount = max(0, $activeEmployees - $participatingCount);
        $completionPct = $activeEmployees > 0 ? round((($completedCount + $waivedCount) / $activeEmployees) * 100, 1) : 0;

        return [
            'window_id' => $window->id,
            'name' => $window->name,
            'plan_year' => $window->plan_year,
            'status' => $window->status,
            'is_open' => $window->isOpen(),
            'start_date' => $window->start_date->toDateString(),
            'close_date' => $window->close_date->toDateString(),
            'effective_date' => $window->effective_date->toDateString(),
            'total_eligible_population' => $activeEmployees,
            'completed' => $completedCount,
            'in_progress' => $inProgressCount,
            'waived' => $waivedCount,
            'not_started' => $notStartedCount,
            'completion_percentage' => $completionPct,
        ];
    }

    public function finalizeWindow(BenefitEnrollmentWindow $window, User $actor): BenefitEnrollmentWindow
    {
        if ($window->status === 'locked') {
            throw ValidationException::withMessages([
                'window' => "Open Enrollment '{$window->name}' is already finalized and locked.",
            ]);
        }

        return DB::transaction(function () use ($window, $actor) {
            // Convert all confirmed elections into active enrollments
            $elections = BenefitElection::where('benefit_enrollment_window_id', $window->id)
                ->whereIn('status', ['confirmed', 'elected'])
                ->get();

            foreach ($elections as $election) {
                $this->electionService->approveElection($election, $actor);
            }

            $window->update([
                'status' => 'locked',
                'updated_by' => $actor->id,
            ]);

            $this->auditService->record(
                tenantId: $window->tenant_id,
                eventType: 'benefit_open_enrollment.finalized',
                action: 'finalize',
                entityType: BenefitEnrollmentWindow::class,
                entityId: $window->id,
                actorId: $actor->id,
                after: $window->toArray()
            );

            return $window;
        });
    }
}
