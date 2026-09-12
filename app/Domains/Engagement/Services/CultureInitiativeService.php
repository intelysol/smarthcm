<?php

namespace App\Domains\Engagement\Services;

use App\Domains\Engagement\Events\CultureInitiativeCompleted;
use App\Domains\Engagement\Events\CultureInitiativeCreated;
use App\Domains\Engagement\Models\CultureInitiative;
use App\Domains\Engagement\Models\CultureInitiativeAction;
use App\Domains\Engagement\Models\EngagementGoal;
use App\Domains\Shared\Services\AuditService;
use Illuminate\Support\Facades\DB;

class CultureInitiativeService
{
    public function __construct(
        protected AuditService $audit
    ) {}

    public function createInitiative(string $tenantId, array $data, ?int $userId = null): CultureInitiative
    {
        return DB::transaction(function () use ($tenantId, $data, $userId) {
            $initiative = CultureInitiative::query()->create([
                'tenant_id' => $tenantId,
                'code' => $data['code'],
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'category' => $data['category'] ?? 'culture',
                'owner_id' => $data['owner_id'] ?? null,
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'] ?? null,
                'status' => 'active',
                'employees_reached' => $data['employees_reached'] ?? 0,
            ]);

            if (! empty($data['actions']) && is_array($data['actions'])) {
                foreach ($data['actions'] as $act) {
                    $this->addAction($initiative, $act);
                }
            }

            CultureInitiativeCreated::dispatch($initiative);

            $this->audit->record(
                $tenantId,
                'CultureInitiativeCreated',
                'create_culture_initiative',
                CultureInitiative::class,
                (string) $initiative->id,
                $userId,
                null,
                ['code' => $initiative->code, 'title' => $initiative->title]
            );

            return $initiative;
        });
    }

    public function addAction(CultureInitiative $initiative, array $data): CultureInitiativeAction
    {
        return CultureInitiativeAction::query()->create([
            'tenant_id' => $initiative->tenant_id,
            'culture_initiative_id' => $initiative->id,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'due_date' => $data['due_date'] ?? null,
            'status' => $data['status'] ?? 'planned',
            'completion_percentage' => $data['completion_percentage'] ?? 0,
        ]);
    }

    public function updateAction(CultureInitiativeAction $action, array $data): CultureInitiativeAction
    {
        $action->update([
            'title' => $data['title'] ?? $action->title,
            'description' => $data['description'] ?? $action->description,
            'due_date' => $data['due_date'] ?? $action->due_date,
            'status' => $data['status'] ?? $action->status,
            'completion_percentage' => $data['completion_percentage'] ?? $action->completion_percentage,
        ]);

        return $action->fresh();
    }

    public function completeInitiative(CultureInitiative $initiative, ?int $userId = null): CultureInitiative
    {
        return DB::transaction(function () use ($initiative, $userId) {
            $initiative->update([
                'status' => 'completed',
            ]);

            CultureInitiativeCompleted::dispatch($initiative);

            $this->audit->record(
                (string) $initiative->tenant_id,
                'CultureInitiativeCompleted',
                'complete_culture_initiative',
                CultureInitiative::class,
                (string) $initiative->id,
                $userId,
                null,
                ['status' => 'completed']
            );

            return $initiative->fresh();
        });
    }

    public function createGoal(string $tenantId, array $data): EngagementGoal
    {
        return EngagementGoal::query()->create([
            'tenant_id' => $tenantId,
            'title' => $data['title'],
            'metric_type' => $data['metric_type'] ?? 'engagement_score',
            'baseline_value' => $data['baseline_value'],
            'target_value' => $data['target_value'],
            'current_value' => $data['current_value'] ?? $data['baseline_value'],
            'deadline' => $data['deadline'],
            'owner_id' => $data['owner_id'] ?? null,
            'status' => 'active',
        ]);
    }

    public function updateGoalProgress(EngagementGoal $goal, float $currentValue): EngagementGoal
    {
        $goal->update([
            'current_value' => $currentValue,
            'status' => ($currentValue >= (float) $goal->target_value) ? 'achieved' : 'active',
        ]);

        return $goal->fresh();
    }
}
