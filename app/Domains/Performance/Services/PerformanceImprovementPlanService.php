<?php

declare(strict_types=1);

namespace App\Domains\Performance\Services;

use App\Domains\Performance\Events\PerformancePipCreated;
use App\Domains\Performance\Models\PerformanceImprovementPlan;
use App\Domains\Performance\Models\PerformanceImprovementPlanAction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PerformanceImprovementPlanService
{
    /**
     * Create a formal Performance Improvement Plan (PIP).
     */
    public function createPip(array $data, ?User $actor = null): PerformanceImprovementPlan
    {
        return DB::transaction(function () use ($data) {
            $pip = PerformanceImprovementPlan::create([
                'tenant_id' => $data['tenant_id'],
                'employee_id' => $data['employee_id'],
                'cycle_id' => $data['cycle_id'] ?? null,
                'status' => 'active',
                'summary' => $data['summary'],
                'details' => $data['details'] ?? [],
                'start_date' => $data['start_date'] ?? Carbon::today()->toDateString(),
                'end_date' => $data['end_date'] ?? Carbon::today()->addDays(60)->toDateString(),
            ]);

            if (!empty($data['actions']) && is_array($data['actions'])) {
                foreach ($data['actions'] as $action) {
                    $this->addAction($pip, $action);
                }
            }

            event(new PerformancePipCreated($pip));

            return $pip->load('actions');
        });
    }

    /**
     * Add action item to PIP.
     */
    public function addAction(PerformanceImprovementPlan $pip, array $actionData): PerformanceImprovementPlanAction
    {
        return PerformanceImprovementPlanAction::create([
            'plan_id' => $pip->id,
            'title' => $actionData['title'],
            'description' => $actionData['description'] ?? null,
            'owner_id' => $actionData['owner_id'] ?? null,
            'due_date' => $actionData['due_date'] ?? null,
            'status' => $actionData['status'] ?? 'planned',
            'completion_percentage' => $actionData['completion_percentage'] ?? 0,
        ]);
    }

    /**
     * Complete or conclude PIP.
     */
    public function concludePip(
        PerformanceImprovementPlan $pip,
        string $outcomeStatus,
        ?string $conclusionNotes = null
    ): PerformanceImprovementPlan {
        $details = $pip->details ?? [];
        $details['conclusion_notes'] = $conclusionNotes;

        $pip->update([
            'status' => $outcomeStatus, // successful, extended, unsuccessful
            'details' => $details,
        ]);

        return $pip->fresh();
    }
}
