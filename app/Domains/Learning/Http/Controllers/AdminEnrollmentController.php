<?php

namespace App\Domains\Learning\Http\Controllers;

use App\Domains\Employee\Models\Employee;
use App\Domains\Learning\Events\LearningEnrollmentApproved;
use App\Domains\Learning\Events\LearningEnrollmentCancelled;
use App\Domains\Learning\Events\LearningEnrollmentCreated;
use App\Domains\Learning\Models\LearningEnrollment;
use App\Domains\Learning\Resources\LearningEnrollmentResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AdminEnrollmentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', LearningEnrollment::class);

        $enrollments = LearningEnrollment::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->when($request->query('course_id'), fn ($q, $c) => $q->where('course_id', $c))
            ->when($request->query('employee_id'), fn ($q, $e) => $q->where('employee_id', $e))
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->with(['employee', 'course', 'session'])
            ->paginate((int) $request->integer('per_page', 25));

        return LearningEnrollmentResource::collection($enrollments)->response();
    }

    public function store(Request $request): JsonResponse
    {
        abort_unless($request->user()?->hasPermission('hcm.learning.enrollment.manage'), 403);

        $request->validate([
            'employee_id' => ['required', 'uuid', 'exists:employees,id'],
            'course_id' => ['required', 'uuid', 'exists:learning_courses,id'],
            'session_id' => ['nullable', 'uuid', 'exists:learning_sessions,id'],
            'enrollment_type' => ['nullable', 'string'],
        ]);

        $enrollment = LearningEnrollment::query()->create([
            'tenant_id' => $request->user()->tenant_id,
            'employee_id' => $request->validated('employee_id'),
            'course_id' => $request->validated('course_id'),
            'session_id' => $request->validated('session_id'),
            'enrollment_type' => $request->validated('enrollment_type', 'hr'),
            'status' => 'enrolled',
            'enrolled_at' => now(),
            'approved_by' => $request->user()->id,
        ]);

        LearningEnrollmentCreated::dispatch($enrollment);

        return LearningEnrollmentResource::make($enrollment->load(['employee', 'course']))->response()->setStatusCode(201);
    }

    public function approve(Request $request, LearningEnrollment $enrollment): JsonResponse
    {
        Gate::authorize('manage', $enrollment);

        $enrollment->update([
            'status' => 'approved',
            'approved_by' => $request->user()->id,
        ]);

        LearningEnrollmentApproved::dispatch($enrollment);

        return LearningEnrollmentResource::make($enrollment)->response();
    }

    public function cancel(Request $request, LearningEnrollment $enrollment): JsonResponse
    {
        Gate::authorize('manage', $enrollment);

        $enrollment->update(['status' => 'cancelled']);
        LearningEnrollmentCancelled::dispatch($enrollment);

        return response()->json(['message' => 'Enrollment cancelled.']);
    }
}
