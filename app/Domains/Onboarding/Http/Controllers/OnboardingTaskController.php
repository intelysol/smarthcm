<?php

namespace App\Domains\Onboarding\Http\Controllers;

use App\Domains\Onboarding\Models\HcmOnboardingCaseTask;
use App\Domains\Onboarding\Services\OnboardingSecurityService;
use App\Domains\Onboarding\Services\OnboardingTaskEngineService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OnboardingTaskController extends Controller
{
    public function __construct(
        protected OnboardingTaskEngineService $taskEngine,
        protected OnboardingSecurityService $securityService
    ) {
    }

    public function complete(Request $request, string $id): JsonResponse
    {
        $task = HcmOnboardingCaseTask::with('case')->findOrFail($id);
        $this->securityService->authorizeCaseAccess($request->user(), $task->case);

        $completed = $this->taskEngine->completeTask(
            $task,
            $request->user()->id,
            $request->input('notes')
        );

        return response()->json($completed);
    }

    public function block(Request $request, string $id): JsonResponse
    {
        $request->validate(['reason' => 'required|string|max:255']);
        $task = HcmOnboardingCaseTask::with('case')->findOrFail($id);
        $this->securityService->authorizeCaseAccess($request->user(), $task->case);

        $blocked = $this->taskEngine->blockTask($task, $request->input('reason'));
        return response()->json($blocked);
    }

    public function addPrerequisite(Request $request, string $id): JsonResponse
    {
        $request->validate(['prerequisite_task_id' => 'required|uuid']);
        $task = HcmOnboardingCaseTask::with('case')->findOrFail($id);
        $prereq = HcmOnboardingCaseTask::findOrFail($request->input('prerequisite_task_id'));

        $this->securityService->authorizeCaseAccess($request->user(), $task->case);
        $this->taskEngine->addDependency($task, $prereq);

        return response()->json(['message' => 'Dependency linked successfully.']);
    }
}
