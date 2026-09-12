<?php

namespace App\Domains\Learning\Http\Controllers;

use App\Domains\Employee\Models\Employee;
use App\Domains\Learning\Events\LearningEnrollmentCancelled;
use App\Domains\Learning\Events\LearningEnrollmentCreated;
use App\Domains\Learning\Models\LearningCourse;
use App\Domains\Learning\Models\LearningEnrollment;
use App\Domains\Learning\Models\LearningSession;
use App\Domains\Learning\Requests\EnrollmentRequest;
use App\Domains\Learning\Resources\LearningCourseResource;
use App\Domains\Learning\Resources\LearningEnrollmentResource;
use App\Domains\Learning\Services\LearningEligibilityService;
use App\Domains\Learning\Services\LearningSessionService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class EmployeeCourseCatalogController extends Controller
{
    public function catalog(Request $request): JsonResponse
    {
        $tenantId = (string) $request->user()->tenant_id;

        $courses = LearningCourse::query()
            ->where('tenant_id', $tenantId)
            ->whereIn('status', ['published', 'active'])
            ->whereIn('visibility', ['public', 'internal', 'mandatory'])
            ->when($request->query('search'), fn ($q, $s) => $q->where('title', 'like', "%{$s}%")->orWhere('description', 'like', "%{$s}%"))
            ->when($request->query('category_id'), fn ($q, $cat) => $q->where('category_id', $cat))
            ->when($request->query('difficulty'), fn ($q, $diff) => $q->where('difficulty', $diff))
            ->when($request->query('delivery_type'), fn ($q, $dt) => $q->where('delivery_type', $dt))
            ->when($request->query('provider_id'), fn ($q, $prov) => $q->where('provider_id', $prov))
            ->when($request->query('language'), fn ($q, $lang) => $q->where('language', $lang))
            ->with(['category', 'provider'])
            ->paginate((int) $request->integer('per_page', 15));

        return LearningCourseResource::collection($courses)->response();
    }

    public function show(Request $request, LearningCourse $course): JsonResponse
    {
        abort_unless($course->tenant_id === $request->user()->tenant_id, 404);

        $course->load([
            'category', 'provider', 'objectives', 'prerequisites',
            'modules.lessons', 'sessions' => fn ($q) => $q->where('status', 'scheduled')
        ]);

        return LearningCourseResource::make($course)->response();
    }

    public function enroll(
        EnrollmentRequest $request,
        LearningCourse $course,
        LearningEligibilityService $eligibilityService,
        LearningSessionService $sessionService
    ): JsonResponse {
        abort_unless($course->tenant_id === $request->user()->tenant_id, 404);
        $employee = Employee::query()->where('user_id', $request->user()->id)->firstOrFail();

        $session = $request->validated('session_id')
            ? LearningSession::query()->findOrFail($request->validated('session_id'))
            : null;

        $check = $eligibilityService->isEligible($employee, $course, $session);
        if (! $check['eligible']) {
            // Check if capacity issue, offer waitlist
            if ($session && in_array('Session has reached maximum capacity.', $check['reasons'], true)) {
                $waitlist = $sessionService->joinWaitlist($employee, $session);
                return response()->json([
                    'message' => 'Session is full. You have been added to the waitlist.',
                    'waitlist' => $waitlist,
                ], 202);
            }

            throw ValidationException::withMessages(['enrollment' => $check['reasons']]);
        }

        $enrollment = LearningEnrollment::query()->create([
            'tenant_id' => $employee->tenant_id,
            'employee_id' => $employee->id,
            'course_id' => $course->id,
            'course_version_id' => $request->validated('course_version_id') ?? $course->versions()->first()?->id,
            'program_id' => $request->validated('program_id'),
            'path_id' => $request->validated('path_id'),
            'session_id' => $session?->id,
            'enrollment_type' => $request->validated('enrollment_type', 'self'),
            'status' => 'enrolled',
            'enrolled_at' => now(),
        ]);

        LearningEnrollmentCreated::dispatch($enrollment);

        return LearningEnrollmentResource::make($enrollment->load(['course', 'session']))->response()->setStatusCode(201);
    }

    public function withdraw(Request $request, LearningEnrollment $enrollment): JsonResponse
    {
        abort_unless($enrollment->tenant_id === $request->user()->tenant_id, 404);
        $employee = Employee::query()->where('user_id', $request->user()->id)->firstOrFail();
        abort_unless((string) $enrollment->employee_id === (string) $employee->id, 403);

        if ($enrollment->status === 'completed') {
            throw ValidationException::withMessages(['enrollment' => 'Completed courses cannot be withdrawn.']);
        }

        $enrollment->update(['status' => 'withdrawn']);
        LearningEnrollmentCancelled::dispatch($enrollment);

        return response()->json(['message' => 'Successfully withdrawn from enrollment.']);
    }
}
