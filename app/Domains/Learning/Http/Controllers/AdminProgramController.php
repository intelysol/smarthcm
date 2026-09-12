<?php

namespace App\Domains\Learning\Http\Controllers;

use App\Domains\Learning\Models\LearningCourse;
use App\Domains\Learning\Models\LearningProgram;
use App\Domains\Learning\Services\LearningProgramPathService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminProgramController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()?->hasPermission('hcm.learning.program.view'), 403);

        $programs = LearningProgram::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->with('programCourses.course')
            ->paginate((int) $request->integer('per_page', 25));

        return response()->json($programs);
    }

    public function store(Request $request, LearningProgramPathService $service): JsonResponse
    {
        abort_unless($request->user()?->hasPermission('hcm.learning.program.manage'), 403);

        $request->validate([
            'code' => ['required', 'string', 'max:50'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'completion_rule' => ['nullable', 'string', 'in:all_required,minimum_credits,minimum_courses'],
            'min_required_credits' => ['nullable', 'numeric'],
            'min_required_courses' => ['nullable', 'integer'],
        ]);

        $program = $service->createProgram($request->user(), $request->all());

        return response()->json(['data' => $program], 201);
    }

    public function addCourse(Request $request, LearningProgram $program, LearningProgramPathService $service): JsonResponse
    {
        abort_unless($request->user()?->hasPermission('hcm.learning.program.manage'), 403);
        abort_unless($program->tenant_id === $request->user()->tenant_id, 404);

        $request->validate(['course_id' => ['required', 'uuid', 'exists:learning_courses,id']]);
        $course = LearningCourse::query()->findOrFail($request->validated('course_id'));

        $item = $service->addCourseToProgram(
            $program,
            $course,
            (bool) $request->boolean('is_required', true),
            (int) $request->integer('sort_order', 0)
        );

        return response()->json(['data' => $item], 201);
    }
}
