<?php

namespace App\Domains\Mobility\Http\Controllers;

use App\Domains\Mobility\Models\MobilityAssignment;
use App\Domains\Mobility\Models\MobilityChange;
use App\Domains\Mobility\Models\MobilityExtension;
use App\Domains\Mobility\Models\MobilityRelocationCase;
use App\Domains\Mobility\Models\MobilityRelocationItem;
use App\Domains\Mobility\Models\MobilityRepatriation;
use App\Domains\Mobility\Models\MobilityTask;
use App\Domains\Mobility\Services\MobilityAiAdvisoryService;
use App\Domains\Mobility\Services\MobilityChangeService;
use App\Domains\Mobility\Services\MobilityCrossDomainCoordinator;
use App\Domains\Mobility\Services\MobilityExtensionService;
use App\Domains\Mobility\Services\MobilityRelocationService;
use App\Domains\Mobility\Services\MobilityTaskService;
use App\Domains\Mobility\Services\RepatriationService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobilityLifecycleController extends Controller
{
    public function __construct(
        protected MobilityRelocationService $relocationService,
        protected MobilityTaskService $taskService,
        protected MobilityExtensionService $extensionService,
        protected MobilityChangeService $changeService,
        protected RepatriationService $repatriationService,
        protected MobilityCrossDomainCoordinator $crossDomainCoordinator,
        protected MobilityAiAdvisoryService $aiAdvisoryService
    ) {}

    public function initiateRelocation(MobilityAssignment $assignment, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'relocation_provider_name' => 'nullable|string|max:120',
            'provider_reference' => 'nullable|string|max:80',
            'target_move_date' => 'nullable|date',
            'family_relocating' => 'sometimes|boolean',
            'relocating_dependent_ids' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);

        $case = $this->relocationService->initiateRelocationCase($assignment, $validated, $request->user());
        return response()->json($case->load('items'), 201);
    }

    public function completeRelocationItem(MobilityRelocationItem $item): JsonResponse
    {
        $updated = $this->relocationService->completeItem($item);
        return response()->json($updated);
    }

    public function generateTasks(MobilityAssignment $assignment): JsonResponse
    {
        $tasks = $this->taskService->generateLifecycleTasks($assignment);
        return response()->json($tasks, 201);
    }

    public function completeTask(MobilityTask $task, Request $request): JsonResponse
    {
        $updated = $this->taskService->completeTask($task, $request->user());
        return response()->json($updated);
    }

    public function requestExtension(MobilityAssignment $assignment, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'proposed_end_date' => 'required|date|after:' . $assignment->planned_end_date,
            'extension_reason' => 'required|string|max:500',
            'additional_estimated_cost' => 'sometimes|numeric|min:0',
        ]);

        $extension = $this->extensionService->requestExtension($assignment, $validated, $request->user());
        return response()->json($extension, 201);
    }

    public function approveExtension(MobilityExtension $extension, Request $request): JsonResponse
    {
        $approved = $this->extensionService->approveExtension($extension, $request->user());
        return response()->json($approved);
    }

    public function requestChange(MobilityAssignment $assignment, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'change_type' => 'required|string|max:60',
            'previous_values' => 'nullable|array',
            'proposed_values' => 'required|array',
            'effective_date' => 'sometimes|date',
            'reason' => 'required|string|max:500',
        ]);

        $change = $this->changeService->requestChange($assignment, $validated, $request->user());
        return response()->json($change, 201);
    }

    public function approveChange(MobilityChange $change, Request $request): JsonResponse
    {
        $approved = $this->changeService->approveChange($change, $request->user());
        return response()->json($approved);
    }

    public function initiateRepatriation(MobilityAssignment $assignment, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'planned_return_date' => 'sometimes|date',
            'outcome_type' => 'sometimes|string|max:60',
            'notes' => 'nullable|string',
        ]);

        $repatriation = $this->repatriationService->initiateRepatriation($assignment, $validated, $request->user());
        return response()->json($repatriation, 201);
    }

    public function completeRepatriation(MobilityRepatriation $repatriation, Request $request): JsonResponse
    {
        $completed = $this->repatriationService->completeRepatriation($repatriation, $request->user());
        return response()->json($completed);
    }

    public function aiBriefing(MobilityAssignment $assignment): JsonResponse
    {
        $briefing = $this->aiAdvisoryService->generateBriefing($assignment);
        return response()->json($briefing);
    }
}
