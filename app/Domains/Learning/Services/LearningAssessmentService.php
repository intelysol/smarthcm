<?php

namespace App\Domains\Learning\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\Events\Services\EventBus;
use App\Domains\Learning\Events\LearningAssessmentFailed;
use App\Domains\Learning\Events\LearningAssessmentPassed;
use App\Domains\Learning\Events\LearningAssessmentStarted;
use App\Domains\Learning\Events\LearningAssessmentSubmitted;
use App\Domains\Learning\Models\LearningAssessment;
use App\Domains\Learning\Models\LearningAssessmentAnswer;
use App\Domains\Learning\Models\LearningAssessmentAttempt;
use App\Domains\Learning\Models\LearningEnrollment;
use App\Domains\Shared\Services\AuditService;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LearningAssessmentService
{
    public function __construct(
        private readonly LearningAssessmentScoringService $scoringService,
        private readonly LearningCompletionService $completionService,
        private readonly AuditService $audit,
        private readonly EventBus $events
    ) {}

    /**
     * Start a new attempt for the employee on the assessment.
     */
    public function startAttempt(User $user, Employee $employee, LearningAssessment $assessment, ?LearningEnrollment $enrollment = null): LearningAssessmentAttempt
    {
        $existingAttemptsCount = LearningAssessmentAttempt::query()
            ->where('tenant_id', $employee->tenant_id)
            ->where('assessment_id', $assessment->id)
            ->where('employee_id', $employee->id)
            ->count();

        if ($assessment->max_attempts && $existingAttemptsCount >= $assessment->max_attempts) {
            throw ValidationException::withMessages([
                'assessment' => "Maximum attempt limit of {$assessment->max_attempts} has been reached."
            ]);
        }

        // Check if there is an in-progress attempt
        $inProgressAttempt = LearningAssessmentAttempt::query()
            ->where('tenant_id', $employee->tenant_id)
            ->where('assessment_id', $assessment->id)
            ->where('employee_id', $employee->id)
            ->where('status', 'in_progress')
            ->first();

        if ($inProgressAttempt) {
            return $inProgressAttempt;
        }

        $attemptNumber = $existingAttemptsCount + 1;

        return DB::transaction(function () use ($user, $employee, $assessment, $enrollment, $attemptNumber) {
            $attempt = LearningAssessmentAttempt::query()->create([
                'tenant_id' => $employee->tenant_id,
                'assessment_id' => $assessment->id,
                'employee_id' => $employee->id,
                'enrollment_id' => $enrollment?->id,
                'attempt_number' => $attemptNumber,
                'started_at' => now(),
                'status' => 'in_progress',
            ]);

            LearningAssessmentStarted::dispatch($attempt);

            $this->audit->record(
                (string) $employee->tenant_id,
                'LearningAssessmentStarted',
                'start_attempt',
                LearningAssessmentAttempt::class,
                (string) $attempt->id,
                $user->id,
                null,
                ['attempt_number' => $attemptNumber, 'assessment_id' => $assessment->id]
            );

            return $attempt;
        });
    }

    /**
     * Submit and grade an in-progress assessment attempt.
     *
     * @param array<string, mixed> $answers [question_id => answer]
     */
    public function submitAttempt(User $user, LearningAssessmentAttempt $attempt, array $answers): LearningAssessmentAttempt
    {
        if ($attempt->status !== 'in_progress') {
            throw ValidationException::withMessages([
                'attempt' => 'This attempt has already been submitted and cannot be resubmitted.'
            ]);
        }

        $assessment = $attempt->assessment;

        return DB::transaction(function () use ($user, $attempt, $assessment, $answers) {
            $grading = $this->scoringService->gradeAssessment($assessment, $answers);

            foreach ($grading['graded_answers'] as $graded) {
                LearningAssessmentAnswer::query()->create([
                    'attempt_id' => $attempt->id,
                    'question_id' => $graded['question_id'],
                    'selected_option_id' => $graded['selected_option_id'],
                    'selected_option_ids' => $graded['selected_option_ids'],
                    'text_answer' => $graded['text_answer'],
                    'numeric_answer' => $graded['numeric_answer'],
                    'is_correct' => $graded['is_correct'],
                    'points_awarded' => $graded['points_awarded'],
                ]);
            }

            $attempt->update([
                'submitted_at' => now(),
                'score_obtained' => $grading['score_obtained'],
                'score_percentage' => $grading['score_percentage'],
                'passed' => $grading['passed'],
                'status' => 'graded',
            ]);

            LearningAssessmentSubmitted::dispatch($attempt);

            if ($grading['passed']) {
                LearningAssessmentPassed::dispatch($attempt);
            } else {
                LearningAssessmentFailed::dispatch($attempt);
            }

            // Update associated enrollment if present
            if ($attempt->enrollment_id) {
                $enrollment = LearningEnrollment::query()->find($attempt->enrollment_id);
                if ($enrollment) {
                    $enrollment->update([
                        'score' => $grading['score_percentage'],
                        'passed' => $grading['passed'],
                    ]);

                    $this->completionService->evaluateCourseCompletion($enrollment);
                }
            }

            $this->audit->record(
                (string) $attempt->tenant_id,
                'LearningAssessmentSubmitted',
                'submit_attempt',
                LearningAssessmentAttempt::class,
                (string) $attempt->id,
                $user->id,
                null,
                [
                    'score_percentage' => $grading['score_percentage'],
                    'passed' => $grading['passed'],
                ]
            );

            return $attempt->fresh('answers');
        });
    }

    /**
     * Get sanitized questions for the employee (anti-tamper: strips is_correct).
     *
     * @return list<array<string, mixed>>
     */
    public function getSanitizedQuestions(LearningAssessment $assessment): array
    {
        $questions = $assessment->questions()->with(['options' => function ($q) {
            $q->orderBy('sort_order')->select(['id', 'question_id', 'option_text', 'sort_order']);
        }])->orderBy('sort_order')->get();

        return $questions->map(function ($q) {
            return [
                'id' => $q->id,
                'question_type' => $q->question_type,
                'question_text' => $q->question_text,
                'points' => (float) $q->points,
                'options' => $q->options->map(fn ($opt) => [
                    'id' => $opt->id,
                    'option_text' => $opt->option_text,
                    'sort_order' => $opt->sort_order,
                ])->values()->all(),
            ];
        })->values()->all();
    }
}
