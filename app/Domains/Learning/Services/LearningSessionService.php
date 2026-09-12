<?php

namespace App\Domains\Learning\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\Events\Services\EventBus;
use App\Domains\Learning\Events\LearningEnrollmentCreated;
use App\Domains\Learning\Events\LearningSessionCancelled;
use App\Domains\Learning\Events\LearningSessionCompleted;
use App\Domains\Learning\Events\LearningSessionScheduled;
use App\Domains\Learning\Models\LearningEnrollment;
use App\Domains\Learning\Models\LearningSession;
use App\Domains\Learning\Models\LearningSessionAttendance;
use App\Domains\Learning\Models\LearningWaitlist;
use App\Domains\Shared\Services\AuditService;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LearningSessionService
{
    public function __construct(
        private readonly AuditService $audit,
        private readonly EventBus $events
    ) {}

    public function scheduleSession(User $actor, array $attributes): LearningSession
    {
        return DB::transaction(function () use ($actor, $attributes) {
            $tenantId = (string) ($attributes['tenant_id'] ?? $actor->tenant_id);

            $session = LearningSession::query()->create([
                ...$attributes,
                'tenant_id' => $tenantId,
                'status' => $attributes['status'] ?? 'scheduled',
            ]);

            LearningSessionScheduled::dispatch($session);

            $this->audit->record(
                $tenantId,
                'LearningSessionScheduled',
                'schedule_session',
                LearningSession::class,
                (string) $session->id,
                $actor->id,
                null,
                [
                    'course_id' => $session->course_id,
                    'start_datetime' => $session->start_datetime->toIso8601String(),
                ]
            );

            return $session;
        });
    }

    public function cancelSession(User $actor, LearningSession $session, string $reason): LearningSession
    {
        return DB::transaction(function () use ($actor, $session, $reason) {
            $session->update(['status' => 'cancelled']);

            // Update enrollments to cancelled
            LearningEnrollment::query()
                ->where('session_id', $session->id)
                ->whereIn('status', ['enrolled', 'requested'])
                ->update(['status' => 'cancelled']);

            LearningSessionCancelled::dispatch($session);

            $this->audit->record(
                (string) $session->tenant_id,
                'LearningSessionCancelled',
                'cancel_session',
                LearningSession::class,
                (string) $session->id,
                $actor->id,
                null,
                ['reason' => $reason]
            );

            return $session;
        });
    }

    public function completeSession(User $actor, LearningSession $session): LearningSession
    {
        return DB::transaction(function () use ($actor, $session) {
            $session->update(['status' => 'completed']);

            LearningSessionCompleted::dispatch($session);

            $this->audit->record(
                (string) $session->tenant_id,
                'LearningSessionCompleted',
                'complete_session',
                LearningSession::class,
                (string) $session->id,
                $actor->id
            );

            return $session;
        });
    }

    public function recordAttendance(
        User $actor,
        LearningSession $session,
        Employee $employee,
        string $status,
        int $attendanceMinutes = 0,
        ?string $notes = null
    ): LearningSessionAttendance {
        return DB::transaction(function () use ($actor, $session, $employee, $status, $attendanceMinutes, $notes) {
            $attendance = LearningSessionAttendance::query()->updateOrCreate(
                [
                    'session_id' => $session->id,
                    'employee_id' => $employee->id,
                ],
                [
                    'tenant_id' => $session->tenant_id,
                    'status' => $status,
                    'check_in' => in_array($status, ['present', 'late'], true) ? now() : null,
                    'check_out' => in_array($status, ['present'], true) ? now() : null,
                    'attendance_minutes' => $attendanceMinutes,
                    'notes' => $notes,
                ]
            );

            $this->audit->record(
                (string) $session->tenant_id,
                'LearningAttendanceRecorded',
                'record_attendance',
                LearningSessionAttendance::class,
                (string) $attendance->id,
                $actor->id,
                null,
                [
                    'session_id' => $session->id,
                    'employee_id' => $employee->id,
                    'status' => $status,
                ]
            );

            return $attendance;
        });
    }

    public function joinWaitlist(Employee $employee, LearningSession $session): LearningWaitlist
    {
        $existing = LearningWaitlist::query()
            ->where('session_id', $session->id)
            ->where('employee_id', $employee->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        $nextPosition = (int) LearningWaitlist::query()
            ->where('session_id', $session->id)
            ->max('position') + 1;

        return LearningWaitlist::query()->create([
            'tenant_id' => $employee->tenant_id,
            'session_id' => $session->id,
            'employee_id' => $employee->id,
            'status' => 'waiting',
            'position' => $nextPosition,
        ]);
    }

    /**
     * Promote next waiting employee on waitlist when capacity is available.
     */
    public function promoteNextOnWaitlist(LearningSession $session): ?LearningEnrollment
    {
        $nextWaitlisted = LearningWaitlist::query()
            ->where('session_id', $session->id)
            ->where('status', 'waiting')
            ->orderBy('position')
            ->first();

        if (! $nextWaitlisted) {
            return null;
        }

        return DB::transaction(function () use ($session, $nextWaitlisted) {
            $nextWaitlisted->update([
                'status' => 'enrolled',
                'offered_at' => now(),
            ]);

            $enrollment = LearningEnrollment::query()->create([
                'tenant_id' => $session->tenant_id,
                'employee_id' => $nextWaitlisted->employee_id,
                'course_id' => $session->course_id,
                'course_version_id' => $session->course_version_id,
                'session_id' => $session->id,
                'enrollment_type' => 'self',
                'status' => 'enrolled',
                'enrolled_at' => now(),
            ]);

            LearningEnrollmentCreated::dispatch($enrollment);

            return $enrollment;
        });
    }
}
