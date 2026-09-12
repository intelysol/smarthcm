<?php

namespace App\Domains\Learning\Http\Controllers;

use App\Domains\Employee\Models\Employee;
use App\Domains\Learning\Models\LearningSession;
use App\Domains\Learning\Requests\SessionCreateRequest;
use App\Domains\Learning\Resources\LearningSessionResource;
use App\Domains\Learning\Services\LearningSessionService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AdminSessionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', LearningSession::class);

        $sessions = LearningSession::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->when($request->query('course_id'), fn ($q, $c) => $q->where('course_id', $c))
            ->when($request->query('status'), fn ($q, $st) => $q->where('status', $st))
            ->with(['course', 'provider', 'instructor', 'venue'])
            ->paginate((int) $request->integer('per_page', 25));

        return LearningSessionResource::collection($sessions)->response();
    }

    public function store(SessionCreateRequest $request, LearningSessionService $service): JsonResponse
    {
        Gate::authorize('manage', LearningSession::class);

        $session = $service->scheduleSession($request->user(), $request->validated());

        return LearningSessionResource::make($session)->response()->setStatusCode(201);
    }

    public function update(Request $request, LearningSession $session): JsonResponse
    {
        Gate::authorize('manage', $session);

        $session->update($request->only(['title', 'start_datetime', 'end_datetime', 'capacity', 'enrollment_deadline', 'status', 'venue_id', 'instructor_id']));

        return LearningSessionResource::make($session->fresh())->response();
    }

    public function recordAttendance(Request $request, LearningSession $session, LearningSessionService $service): JsonResponse
    {
        Gate::authorize('attendance', $session);

        $request->validate([
            'employee_id' => ['required', 'uuid', 'exists:employees,id'],
            'status' => ['required', 'string', 'in:present,absent,late,excused,partial'],
            'attendance_minutes' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $employee = Employee::query()->findOrFail($request->validated('employee_id'));
        $attendance = $service->recordAttendance(
            $request->user(),
            $session,
            $employee,
            $request->string('status')->toString(),
            $request->integer('attendance_minutes', 0),
            $request->input('notes')
        );

        return response()->json(['data' => $attendance]);
    }
}
