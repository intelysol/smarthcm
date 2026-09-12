<?php

namespace App\Domains\Learning\Http\Controllers;

use App\Domains\Employee\Models\Employee;
use App\Domains\Learning\Events\LearningProgressUpdated;
use App\Domains\Learning\Events\LearningStarted;
use App\Domains\Learning\Models\LearningEnrollment;
use App\Domains\Learning\Models\LearningItem;
use App\Domains\Learning\Models\LearningProgress;
use App\Domains\Learning\Requests\ProgressUpdateRequest;
use App\Domains\Learning\Resources\LearningEnrollmentResource;
use App\Domains\Learning\Services\LearningCompletionService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeLearningProgressController extends Controller
{
    public function enrollments(Request $request): JsonResponse
    {
        $employee = Employee::query()->where('user_id', $request->user()->id)->firstOrFail();

        $enrollments = LearningEnrollment::query()
            ->where('tenant_id', $employee->tenant_id)
            ->where('employee_id', $employee->id)
            ->when($request->query('status'), fn ($q, $st) => $q->where('status', $st))
            ->with(['course', 'session', 'progressRecords'])
            ->paginate((int) $request->integer('per_page', 20));

        return LearningEnrollmentResource::collection($enrollments)->response();
    }

    public function progress(Request $request): JsonResponse
    {
        $employee = Employee::query()->where('user_id', $request->user()->id)->firstOrFail();

        $progress = LearningProgress::query()
            ->where('tenant_id', $employee->tenant_id)
            ->where('employee_id', $employee->id)
            ->when($request->query('enrollment_id'), fn ($q, $eId) => $q->where('enrollment_id', $eId))
            ->with('item')
            ->get();

        return response()->json(['data' => $progress]);
    }

    public function updateItemProgress(
        ProgressUpdateRequest $request,
        LearningItem $item,
        LearningCompletionService $completionService
    ): JsonResponse {
        $employee = Employee::query()->where('user_id', $request->user()->id)->firstOrFail();
        $enrollment = LearningEnrollment::query()
            ->where('id', $request->validated('enrollment_id'))
            ->where('employee_id', $employee->id)
            ->firstOrFail();

        $status = $request->validated('status');
        $percentage = (float) $request->validated('progress_percentage');

        if (! $status) {
            $status = $percentage >= 100.0 ? 'completed' : ($percentage > 0 ? 'in_progress' : 'not_started');
        }

        $progress = LearningProgress::query()->updateOrCreate(
            [
                'enrollment_id' => $enrollment->id,
                'learning_item_id' => $item->id,
            ],
            [
                'tenant_id' => $employee->tenant_id,
                'employee_id' => $employee->id,
                'status' => $status,
                'progress_percentage' => $percentage,
                'time_spent_seconds' => $request->integer('time_spent_seconds', 0),
                'video_watched_percentage' => (float) $request->validated('video_watched_percentage', 0),
                'started_at' => $status !== 'not_started' ? now() : null,
                'completed_at' => $status === 'completed' ? now() : null,
                'last_accessed_at' => now(),
            ]
        );

        if ($enrollment->status === 'enrolled') {
            $enrollment->update([
                'status' => 'in_progress',
                'started_at' => now(),
            ]);
            LearningStarted::dispatch($enrollment);
        }

        LearningProgressUpdated::dispatch($progress);

        // Recalculate enrollment overall progress
        $totalItems = $enrollment->course?->items()->count() ?? 1;
        $completedItems = $enrollment->progressRecords()->where('status', 'completed')->count();
        $overallPercentage = $totalItems > 0 ? round(($completedItems / $totalItems) * 100, 2) : 0.0;

        $enrollment->update(['progress_percentage' => $overallPercentage]);

        // Evaluate course completion
        $completionService->evaluateCourseCompletion($enrollment);

        return response()->json([
            'data' => $progress,
            'enrollment_progress_percentage' => $overallPercentage,
        ]);
    }
}
