<?php

namespace App\Domains\Learning\Http\Controllers;

use App\Domains\Employee\Models\Employee;
use App\Domains\Learning\Models\LearningRequirement;
use App\Domains\Learning\Models\LearningRequirementAssignment;
use App\Domains\Learning\Requests\RequirementCreateRequest;
use App\Domains\Learning\Resources\LearningRequirementResource;
use App\Domains\Learning\Services\LearningRequirementService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AdminRequirementController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()?->hasPermission('hcm.learning.requirement.view'), 403);

        $requirements = LearningRequirement::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->with(['course', 'assignments'])
            ->paginate((int) $request->integer('per_page', 25));

        return LearningRequirementResource::collection($requirements)->response();
    }

    public function store(RequirementCreateRequest $request): JsonResponse
    {
        abort_unless($request->user()?->hasPermission('hcm.learning.requirement.manage'), 403);

        $requirement = LearningRequirement::query()->create([
            ...$request->validated(),
            'tenant_id' => $request->user()->tenant_id,
            'status' => 'active',
        ]);

        return LearningRequirementResource::make($requirement->load('course'))->response()->setStatusCode(201);
    }

    public function assignments(Request $request, LearningRequirement $requirement): JsonResponse
    {
        abort_unless($request->user()?->hasPermission('hcm.learning.requirement.view'), 403);
        abort_unless($requirement->tenant_id === $request->user()->tenant_id, 404);

        $assignments = $requirement->assignments()->with('employee')->paginate((int) $request->integer('per_page', 25));

        return response()->json($assignments);
    }

    public function waive(Request $request, LearningRequirementAssignment $assignment, LearningRequirementService $service): JsonResponse
    {
        abort_unless($request->user()?->hasPermission('hcm.learning.requirement.manage'), 403);
        abort_unless($assignment->tenant_id === $request->user()->tenant_id, 404);

        $request->validate(['reason' => ['required', 'string', 'max:500']]);

        $waived = $service->waiveRequirement($request->user(), $assignment, $request->string('reason')->toString());

        return response()->json(['data' => $waived]);
    }
}
