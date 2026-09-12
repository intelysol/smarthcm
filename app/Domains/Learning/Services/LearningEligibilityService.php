<?php

namespace App\Domains\Learning\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\Learning\Models\LearningCertificate;
use App\Domains\Learning\Models\LearningCourse;
use App\Domains\Learning\Models\LearningEnrollment;
use App\Domains\Learning\Models\LearningSession;
use Illuminate\Support\Carbon;

class LearningEligibilityService
{
    /**
     * Check if employee is eligible to enroll in course.
     *
     * @return array{eligible: bool, reasons: list<string>}
     */
    public function isEligible(Employee $employee, LearningCourse $course, ?LearningSession $session = null): array
    {
        $reasons = [];

        // Check prerequisites
        $prereqCheck = $this->checkPrerequisites($employee, $course);
        if (! $prereqCheck['passed']) {
            $reasons = array_merge($reasons, $prereqCheck['missing']);
        }

        // Check active enrollment duplicate
        $hasActiveEnrollment = LearningEnrollment::query()
            ->where('tenant_id', $employee->tenant_id)
            ->where('employee_id', $employee->id)
            ->where('course_id', $course->id)
            ->whereIn('status', ['requested', 'approved', 'enrolled', 'in_progress'])
            ->exists();

        if ($hasActiveEnrollment) {
            $reasons[] = 'Employee already has an active enrollment in this course.';
        }

        // Check session capacity and schedule conflict if session is provided
        if ($session) {
            if (! $this->checkCapacity($session)) {
                $reasons[] = 'Session has reached maximum capacity.';
            }

            if (! $this->checkScheduleConflict($employee, $session)) {
                $reasons[] = 'Session conflicts with another scheduled session.';
            }

            if ($session->enrollment_deadline && Carbon::now()->isAfter($session->enrollment_deadline)) {
                $reasons[] = 'Session enrollment deadline has passed.';
            }
        }

        return [
            'eligible' => empty($reasons),
            'reasons' => $reasons,
        ];
    }

    /**
     * @return array{passed: bool, missing: list<string>}
     */
    public function checkPrerequisites(Employee $employee, LearningCourse $course): array
    {
        $missing = [];
        $prerequisites = $course->prerequisites()->where('is_mandatory', true)->get();

        foreach ($prerequisites as $prereq) {
            switch ($prereq->prerequisite_type) {
                case 'course':
                    $completed = LearningEnrollment::query()
                        ->where('tenant_id', $employee->tenant_id)
                        ->where('employee_id', $employee->id)
                        ->where('course_id', $prereq->prerequisite_id)
                        ->where('status', 'completed')
                        ->exists();

                    if (! $completed) {
                        $targetCourse = LearningCourse::query()->find($prereq->prerequisite_id);
                        $missing[] = 'Missing prerequisite course: ' . ($targetCourse?->title ?? 'Course');
                    }
                    break;

                case 'certification':
                    $hasCert = LearningCertificate::query()
                        ->where('tenant_id', $employee->tenant_id)
                        ->where('employee_id', $employee->id)
                        ->where('status', 'active')
                        ->where(function ($q) use ($prereq) {
                            $q->where('id', $prereq->prerequisite_id)
                              ->orWhere('course_id', $prereq->prerequisite_id);
                        })
                        ->where(fn ($q) => $q->whereNull('expiry_date')->orWhere('expiry_date', '>=', now()->toDateString()))
                        ->exists();

                    if (! $hasCert) {
                        $missing[] = 'Missing required active certification.';
                    }
                    break;

                case 'competency':
                    // Competency integration: check employee skills or assessment
                    break;
            }
        }

        return [
            'passed' => empty($missing),
            'missing' => $missing,
        ];
    }

    public function checkCapacity(LearningSession $session): bool
    {
        if ($session->capacity === null || $session->capacity === 0) {
            return true;
        }

        $enrolledCount = LearningEnrollment::query()
            ->where('session_id', $session->id)
            ->whereIn('status', ['enrolled', 'in_progress'])
            ->count();

        return $enrolledCount < $session->capacity;
    }

    public function checkScheduleConflict(Employee $employee, LearningSession $session): bool
    {
        return ! LearningEnrollment::query()
            ->where('tenant_id', $employee->tenant_id)
            ->where('employee_id', $employee->id)
            ->whereIn('status', ['enrolled', 'in_progress'])
            ->whereHas('session', function ($q) use ($session) {
                $q->where('id', '!=', $session->id)
                  ->where('status', 'scheduled')
                  ->where(function ($sq) use ($session) {
                      $sq->whereBetween('start_datetime', [$session->start_datetime, $session->end_datetime])
                        ->orWhereBetween('end_datetime', [$session->start_datetime, $session->end_datetime])
                        ->orWhere(function ($tsq) use ($session) {
                            $tsq->where('start_datetime', '<=', $session->start_datetime)
                                ->where('end_datetime', '>=', $session->end_datetime);
                        });
                  });
            })
            ->exists();
    }

    public function checkPolicy(Employee $employee, LearningCourse $course, string $enrollmentType): bool
    {
        if ($course->visibility === 'restricted' && ! in_array($enrollmentType, ['manager', 'hr', 'mandatory'], true)) {
            return false;
        }

        return true;
    }
}
