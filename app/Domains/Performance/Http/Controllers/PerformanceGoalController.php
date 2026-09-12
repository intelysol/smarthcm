<?php

namespace App\Domains\Performance\Http\Controllers;

use App\Domains\Employee\Models\Employee;
use App\Domains\Performance\Models\PerformanceGoal;
use App\Domains\Performance\Requests\PerformanceGoalProgressRequest;
use App\Domains\Performance\Requests\PerformanceGoalRequest;
use App\Domains\Performance\Resources\PerformanceGoalResource;
use App\Domains\Performance\Services\PerformanceGoalService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PerformanceGoalController extends Controller
{
    public function index(Request $request): JsonResponse { abort_unless($request->user()?->hasPermission('hcm.performance.goal.view'), 403); $goals = PerformanceGoal::query()->when($request->query('cycle_id'), fn ($q, $id) => $q->where('cycle_id', $id))->when($request->query('employee_id'), fn ($q, $id) => $q->where('employee_id', $id))->with('milestones')->paginate((int) $request->integer('per_page', 25)); return PerformanceGoalResource::collection($goals)->response(); }
    public function myIndex(Request $request): JsonResponse { $employee = Employee::query()->where('user_id', $request->user()->id)->firstOrFail(); return PerformanceGoalResource::collection(PerformanceGoal::query()->where('employee_id', $employee->id)->with('milestones')->paginate((int) $request->integer('per_page', 25)))->response(); }
    public function store(PerformanceGoalRequest $request, PerformanceGoalService $service): JsonResponse { Gate::authorize('create', PerformanceGoal::class); return PerformanceGoalResource::make($service->create($request->user(), $request->validated()))->response()->setStatusCode(201); }
    public function show(PerformanceGoal $goal): PerformanceGoalResource { Gate::authorize('view', $goal); return PerformanceGoalResource::make($goal->load(['parent', 'children', 'milestones', 'progressRecords'])); }
    public function update(PerformanceGoalRequest $request, PerformanceGoal $goal, PerformanceGoalService $service): PerformanceGoalResource { Gate::authorize('update', $goal); return PerformanceGoalResource::make($service->update($request->user(), $goal, $request->safe()->except('version'), (int) $request->validated('version'))); }
    public function progress(PerformanceGoalProgressRequest $request, PerformanceGoal $goal, PerformanceGoalService $service): JsonResponse { Gate::authorize('update', $goal); $progress = $service->recordProgress($request->user(), $goal, $request->float('new_value'), $request->float('progress_percentage'), $request->input('comment'), $request->string('source')->toString()); return response()->json(['data' => $progress], 201); }
}
