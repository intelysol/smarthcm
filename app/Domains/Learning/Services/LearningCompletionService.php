<?php

namespace App\Domains\Learning\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\Events\Services\EventBus;
use App\Domains\Learning\Events\LearningCompleted;
use App\Domains\Learning\Models\EmployeeLearningRecord;
use App\Domains\Learning\Models\LearningCourse;
use App\Domains\Learning\Models\LearningEnrollment;
use App\Domains\Learning\Models\LearningPath;
use App\Domains\Learning\Models\LearningProgram;
use App\Domains\Learning\Models\LearningRequirementAssignment;
use App\Domains\Learning\Models\LearningSessionAttendance;
use App\Domains\Shared\Services\AuditService;
use Illuminate\Support\Facades\DB;

class LearningCompletionService
{
    public function __construct(
        private readonly LearningCertificateService $certificateService,
        private readonly LearningCreditService $creditService,
        private readonly AuditService $audit,
        private readonly EventBus $events
    ) {}

    /**
     * Evaluate and execute course completion for an enrollment.
     */
    public function evaluateCourseCompletion(LearningEnrollment $enrollment): bool
    {
        if ($enrollment->status === 'completed') {
            return true;
        }

        $course = $enrollment->course;
        if (! $course) {
            return false;
        }

        // 1. Check Mandatory Items
        $mandatoryItems = $course->items()->where('is_mandatory', true)->get();
        if ($mandatoryItems->isNotEmpty()) {
            $completedItemIds = $enrollment->progressRecords()
                ->where('status', 'completed')
                ->pluck('learning_item_id')
                ->all();

            foreach ($mandatoryItems as $item) {
                if (! in_array($item->id, $completedItemIds, true)) {
                    return false;
                }
            }
        }

        // 2. Check Assessment Passing Criteria
        if ($course->passing_score !== null && $course->passing_score > 0) {
            $latestPassedAttempt = $enrollment->attempts()
                ->where('passed', true)
                ->latest('submitted_at')
                ->first();

            if (! $latestPassedAttempt && ($enrollment->score === null || $enrollment->score < (float) $course->passing_score)) {
                return false;
            }
        }

        // 3. Check Attendance Criteria
        if ($course->requires_attendance && $enrollment->session_id) {
            $attendance = LearningSessionAttendance::query()
                ->where('session_id', $enrollment->session_id)
                ->where('employee_id', $enrollment->employee_id)
                ->first();

            if (! $attendance || ! in_array($attendance->status, ['present', 'excused'], true)) {
                return false;
            }
        }

        // All criteria satisfied -> Complete Enrollment!
        return DB::transaction(function () use ($enrollment, $course) {
            $enrollment->update([
                'status' => 'completed',
                'progress_percentage' => 100.0,
                'completed_at' => now(),
            ]);

            // Issue Certificate
            $certificate = $this->certificateService->issueCertificate(
                $enrollment->employee,
                $course,
                $enrollment->courseVersion,
                $enrollment
            );

            // Award Credits
            if ((float) $course->credit_points > 0) {
                $this->creditService->awardCredits(
                    $enrollment->employee,
                    (float) $course->credit_points,
                    'course_completion',
                    $course->id,
                    "Completed course: {$course->title}"
                );
            }

            // Create/Update Employee Learning Record (Transcript)
            $this->updateLearningRecord($enrollment, $certificate?->id);

            // Update Requirement Assignments if this was mandatory
            LearningRequirementAssignment::query()
                ->where('tenant_id', $enrollment->tenant_id)
                ->where('employee_id', $enrollment->employee_id)
                ->where('course_id', $course->id)
                ->whereIn('status', ['required', 'assigned', 'enrolled', 'in_progress', 'overdue'])
                ->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                    'enrollment_id' => $enrollment->id,
                ]);

            LearningCompleted::dispatch($enrollment);

            $this->audit->record(
                (string) $enrollment->tenant_id,
                'LearningCompleted',
                'complete_course',
                LearningEnrollment::class,
                (string) $enrollment->id,
                null,
                null,
                [
                    'course_id' => $course->id,
                    'score' => $enrollment->score,
                    'certificate_id' => $certificate?->id,
                ]
            );

            $this->events->publish([
                'tenant_id' => $enrollment->tenant_id,
                'event_type' => 'LearningCompleted',
                'event_version' => 1,
                'aggregate_type' => 'learning_enrollment',
                'aggregate_id' => $enrollment->id,
                'source_module' => 'learning',
                'payload' => [
                    'employee_id' => $enrollment->employee_id,
                    'course_id' => $course->id,
                    'completed_at' => now()->toIso8601String(),
                ],
            ]);

            return true;
        });
    }

    public function evaluateProgramCompletion(Employee $employee, LearningProgram $program): bool
    {
        $programCourses = $program->programCourses;
        $requiredCourseIds = $programCourses->where('is_required', true)->pluck('course_id')->all();

        $completedCourseIds = EmployeeLearningRecord::query()
            ->where('tenant_id', $employee->tenant_id)
            ->where('employee_id', $employee->id)
            ->where('status', 'completed')
            ->pluck('course_id')
            ->all();

        if ($program->completion_rule === 'all_required') {
            foreach ($requiredCourseIds as $cId) {
                if (! in_array($cId, $completedCourseIds, true)) {
                    return false;
                }
            }
            return true;
        }

        if ($program->completion_rule === 'minimum_courses') {
            $matchingCount = count(array_intersect($programCourses->pluck('course_id')->all(), $completedCourseIds));
            return $matchingCount >= ($program->min_required_courses ?? 1);
        }

        if ($program->completion_rule === 'minimum_credits') {
            $totalEarned = LearningCourse::query()
                ->whereIn('id', array_intersect($programCourses->pluck('course_id')->all(), $completedCourseIds))
                ->sum('credit_points');
            return (float) $totalEarned >= (float) ($program->min_required_credits ?? $program->total_credits);
        }

        return false;
    }

    public function evaluatePathCompletion(Employee $employee, LearningPath $path): bool
    {
        $items = $path->items()->where('requirement_level', 'required')->get();
        $completedCourseIds = EmployeeLearningRecord::query()
            ->where('tenant_id', $employee->tenant_id)
            ->where('employee_id', $employee->id)
            ->where('status', 'completed')
            ->pluck('course_id')
            ->all();

        foreach ($items as $item) {
            if ($item->item_type === 'course' && ! in_array($item->item_id, $completedCourseIds, true)) {
                return false;
            }
        }

        return true;
    }

    public function updateLearningRecord(LearningEnrollment $enrollment, ?string $certificateId = null): EmployeeLearningRecord
    {
        $course = $enrollment->course;

        return EmployeeLearningRecord::query()->updateOrCreate(
            [
                'tenant_id' => $enrollment->tenant_id,
                'employee_id' => $enrollment->employee_id,
                'course_id' => $enrollment->course_id,
            ],
            [
                'course_version_id' => $enrollment->course_version_id,
                'provider_id' => $course?->provider_id,
                'enrollment_id' => $enrollment->id,
                'certificate_id' => $certificateId,
                'completion_date' => now()->toDateString(),
                'final_score' => $enrollment->score,
                'credits_awarded' => (float) ($course?->credit_points ?? 0),
                'learning_hours' => (float) ($course?->duration ?? 0),
                'status' => 'completed',
            ]
        );
    }
}
