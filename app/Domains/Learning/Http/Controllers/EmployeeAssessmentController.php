<?php

namespace App\Domains\Learning\Http\Controllers;

use App\Domains\Employee\Models\Employee;
use App\Domains\Learning\Models\LearningAssessment;
use App\Domains\Learning\Models\LearningAssessmentAttempt;
use App\Domains\Learning\Models\LearningEnrollment;
use App\Domains\Learning\Requests\AssessmentSubmitRequest;
use App\Domains\Learning\Resources\LearningAssessmentResource;
use App\Domains\Learning\Services\LearningAssessmentService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeAssessmentController extends Controller
{
    public function show(Request $request, LearningAssessment $assessment, LearningAssessmentService $service): JsonResponse
    {
        abort_unless($assessment->tenant_id === $request->user()->tenant_id, 404);

        $sanitizedQuestions = $service->getSanitizedQuestions($assessment);

        return response()->json([
            'data' => [
                'assessment' => LearningAssessmentResource::make($assessment),
                'questions' => $sanitizedQuestions,
            ]
        ]);
    }

    public function startAttempt(Request $request, LearningAssessment $assessment, LearningAssessmentService $service): JsonResponse
    {
        abort_unless($assessment->tenant_id === $request->user()->tenant_id, 404);
        $employee = Employee::query()->where('user_id', $request->user()->id)->firstOrFail();

        $enrollment = $request->query('enrollment_id')
            ? LearningEnrollment::query()->where('employee_id', $employee->id)->find($request->query('enrollment_id'))
            : null;

        $attempt = $service->startAttempt($request->user(), $employee, $assessment, $enrollment);
        $sanitizedQuestions = $service->getSanitizedQuestions($assessment);

        return response()->json([
            'data' => [
                'attempt' => $attempt,
                'questions' => $sanitizedQuestions,
            ]
        ], 201);
    }

    public function submitAttempt(
        AssessmentSubmitRequest $request,
        LearningAssessmentAttempt $attempt,
        LearningAssessmentService $service
    ): JsonResponse {
        abort_unless($attempt->tenant_id === $request->user()->tenant_id, 404);
        $employee = Employee::query()->where('user_id', $request->user()->id)->firstOrFail();
        abort_unless((string) $attempt->employee_id === (string) $employee->id, 403);

        $submittedAttempt = $service->submitAttempt($request->user(), $attempt, $request->validated('answers'));

        return response()->json([
            'data' => [
                'attempt_id' => $submittedAttempt->id,
                'score_obtained' => (float) $submittedAttempt->score_obtained,
                'score_percentage' => (float) $submittedAttempt->score_percentage,
                'passed' => (bool) $submittedAttempt->passed,
                'status' => $submittedAttempt->status,
                'submitted_at' => $submittedAttempt->submitted_at?->toIso8601String(),
                'answers' => $submittedAttempt->answers,
            ]
        ]);
    }
}
