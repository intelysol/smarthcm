<?php

namespace App\Domains\Learning\Http\Controllers;

use App\Domains\Employee\Models\Employee;
use App\Domains\Learning\Models\LearningDevelopmentActivity;
use App\Domains\Learning\Models\LearningDevelopmentPlan;
use App\Domains\Learning\Services\LearningDevelopmentPlanService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeDevelopmentPlanController extends Controller
{
    public function __construct(protected LearningDevelopmentPlanService $idpService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $employee = Employee::where('user_id', $request->user()->id)->firstOrFail();
        $plans = LearningDevelopmentPlan::where('employee_id', $employee->id)
            ->with('activities')
            ->latest()
            ->get();

        return response()->json($plans);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:150',
            'goal' => 'required|string',
            'target_completion_date' => 'required|date',
            'skill_target' => 'nullable|string|max:100',
            'competency_target' => 'nullable|string|max:100',
            'target_level' => 'nullable|string|max:50',
            'activities' => 'nullable|array',
        ]);

        $employee = Employee::where('user_id', $request->user()->id)->firstOrFail();
        $data = array_merge($request->all(), [
            'tenant_id' => $employee->tenant_id,
            'employee_id' => $employee->id,
            'manager_id' => $employee->reporting_manager_id,
        ]);

        $plan = $this->idpService->createPlan($data);
        return response()->json($plan, 201);
    }

    public function addActivity(Request $request, string $planId): JsonResponse
    {
        $request->validate([
            'activity_type' => 'required|string|max:40',
            'title' => 'required|string|max:150',
            'course_id' => 'nullable|uuid',
            'target_date' => 'nullable|date',
        ]);

        $employee = Employee::where('user_id', $request->user()->id)->firstOrFail();
        $plan = LearningDevelopmentPlan::where('id', $planId)
            ->where('employee_id', $employee->id)
            ->firstOrFail();

        $activity = $this->idpService->addActivity($plan, $request->all());
        return response()->json($activity, 201);
    }

    public function completeActivity(Request $request, string $activityId): JsonResponse
    {
        $activity = LearningDevelopmentActivity::findOrFail($activityId);
        $updated = $this->idpService->completeActivity($activity, $request->input('evidence_notes'));
        return response()->json($updated);
    }
}
