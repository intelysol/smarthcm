<?php

namespace App\Domains\Learning\Http\Controllers;

use App\Domains\Learning\Models\LearningCourse;
use App\Domains\Learning\Requests\CourseCreateRequest;
use App\Domains\Learning\Requests\CourseUpdateRequest;
use App\Domains\Learning\Resources\LearningCourseResource;
use App\Domains\Learning\Services\LearningCourseService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AdminCourseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', LearningCourse::class);

        $courses = LearningCourse::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->when($request->query('status'), fn ($q, $st) => $q->where('status', $st))
            ->when($request->query('category_id'), fn ($q, $c) => $q->where('category_id', $c))
            ->when($request->query('delivery_type'), fn ($q, $dt) => $q->where('delivery_type', $dt))
            ->when($request->query('search'), fn ($q, $s) => $q->where('title', 'like', "%{$s}%")->orWhere('code', 'like', "%{$s}%"))
            ->with(['category', 'provider', 'versions'])
            ->paginate((int) $request->integer('per_page', 25));

        return LearningCourseResource::collection($courses)->response();
    }

    public function store(CourseCreateRequest $request, LearningCourseService $service): JsonResponse
    {
        Gate::authorize('create', LearningCourse::class);

        $course = $service->create($request->user(), $request->validated());

        return LearningCourseResource::make($course)->response()->setStatusCode(201);
    }

    public function show(Request $request, LearningCourse $course): JsonResponse
    {
        Gate::authorize('view', $course);

        $course->load(['category', 'provider', 'versions', 'objectives', 'prerequisites', 'modules.lessons.items', 'assessments', 'sessions']);

        return LearningCourseResource::make($course)->response();
    }

    public function update(CourseUpdateRequest $request, LearningCourse $course, LearningCourseService $service): JsonResponse
    {
        Gate::authorize('update', $course);

        $updated = $service->update($request->user(), $course, $request->validated());

        return LearningCourseResource::make($updated)->response();
    }

    public function publish(Request $request, LearningCourse $course, LearningCourseService $service): JsonResponse
    {
        Gate::authorize('publish', $course);

        $published = $service->transition($request->user(), $course, 'published');

        return LearningCourseResource::make($published)->response();
    }

    public function version(Request $request, LearningCourse $course, LearningCourseService $service): JsonResponse
    {
        Gate::authorize('update', $course);

        $version = $service->createVersion($request->user(), $course, $request->input('change_log'));

        return response()->json(['data' => $version], 201);
    }

    public function addModule(Request $request, LearningCourse $course, LearningCourseService $service): JsonResponse
    {
        Gate::authorize('update', $course);

        $request->validate(['title' => ['required', 'string', 'max:255']]);
        $module = $service->addModule($course, $request->only(['title', 'description', 'sort_order', 'course_version_id']));

        return response()->json(['data' => $module], 201);
    }

    public function addObjective(Request $request, LearningCourse $course, LearningCourseService $service): JsonResponse
    {
        Gate::authorize('update', $course);

        $request->validate(['objective' => ['required', 'string', 'max:255']]);
        $obj = $service->addObjective($course, $request->only(['objective', 'description', 'expected_outcome', 'sort_order']));

        return response()->json(['data' => $obj], 201);
    }

    public function addPrerequisite(Request $request, LearningCourse $course, LearningCourseService $service): JsonResponse
    {
        Gate::authorize('update', $course);

        $request->validate([
            'prerequisite_type' => ['required', 'string', 'in:course,certification,competency,learning_path'],
            'prerequisite_id' => ['required', 'uuid'],
        ]);

        $prereq = $service->addPrerequisite($course, $request->only(['prerequisite_type', 'prerequisite_id', 'is_mandatory', 'min_grade_or_level']));

        return response()->json(['data' => $prereq], 201);
    }
}
