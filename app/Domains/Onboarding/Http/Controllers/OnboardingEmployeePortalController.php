<?php

namespace App\Domains\Onboarding\Http\Controllers;

use App\Domains\Onboarding\Models\HcmOnboardingCase;
use App\Domains\Onboarding\Models\HcmOnboardingCaseTask;
use App\Domains\Onboarding\Models\HcmOnboardingForm;
use App\Domains\Onboarding\Services\OnboardingPolicyAndFormService;
use App\Domains\Onboarding\Services\OnboardingSecurityService;
use App\Domains\Onboarding\Services\OnboardingTaskEngineService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OnboardingEmployeePortalController extends Controller
{
    public function __construct(
        protected OnboardingTaskEngineService $taskEngine,
        protected OnboardingPolicyAndFormService $policyAndFormService,
        protected OnboardingSecurityService $securityService
    ) {
    }

    public function portalWeb(Request $request): View
    {
        $employeeId = $request->user()->employee_id;
        $case = HcmOnboardingCase::where('employee_id', $employeeId)
            ->with(['employee.department', 'tasks', 'documentRequirements', 'policyAcknowledgements'])
            ->first();

        return view('onboarding.employee.portal', compact('case'));
    }

    public function getMyCase(Request $request): JsonResponse
    {
        $employeeId = $request->user()->employee_id;
        if (!$employeeId) {
            throw ValidationException::withMessages(['employee' => 'User does not have an associated employee profile.']);
        }

        $case = HcmOnboardingCase::where('employee_id', $employeeId)
            ->with(['tasks', 'documentRequirements', 'policyAcknowledgements', 'buddyAssignment.buddy'])
            ->firstOrFail();

        return response()->json($case);
    }

    public function getMyTasks(Request $request): JsonResponse
    {
        $employeeId = $request->user()->employee_id;
        $case = HcmOnboardingCase::where('employee_id', $employeeId)->firstOrFail();

        $tasks = $case->tasks()->where('owner_role', 'employee')->get();
        return response()->json($tasks);
    }

    public function completeMyTask(Request $request, string $taskId): JsonResponse
    {
        $employeeId = $request->user()->employee_id;
        $task = HcmOnboardingCaseTask::with('case')->findOrFail($taskId);

        if ((string) $task->case->employee_id !== (string) $employeeId) {
            return response()->json(['error' => 'Unauthorized: This task does not belong to your onboarding case.'], 403);
        }

        $completed = $this->taskEngine->completeTask($task, $request->user()->id, $request->input('notes'));
        return response()->json($completed);
    }

    public function acknowledgeMyPolicy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'policy_code' => 'required|string|max:50',
            'policy_title' => 'required|string|max:150',
            'policy_version' => 'nullable|string|max:20',
        ]);

        $employeeId = $request->user()->employee_id;
        $case = HcmOnboardingCase::where('employee_id', $employeeId)->firstOrFail();

        $ack = $this->policyAndFormService->acknowledgePolicy(
            $case,
            $validated['policy_code'],
            $validated['policy_title'],
            $validated['policy_version'] ?? 'v1.0',
            $request->ip()
        );

        return response()->json($ack);
    }

    public function submitMyForm(Request $request, string $formId): JsonResponse
    {
        $employeeId = $request->user()->employee_id;
        $case = HcmOnboardingCase::where('employee_id', $employeeId)->firstOrFail();
        $form = HcmOnboardingForm::findOrFail($formId);

        $submission = $this->policyAndFormService->submitDigitalForm(
            $case,
            $form,
            $request->input('form_data', [])
        );

        return response()->json($submission);
    }
}
