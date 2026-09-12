<?php

namespace App\Domains\Engagement\Services;

use App\Domains\Engagement\Events\EngagementActionItemCompleted;
use App\Domains\Engagement\Events\EngagementActionPlanCreated;
use App\Domains\Engagement\Models\EngagementActionItem;
use App\Domains\Engagement\Models\EngagementActionPlan;
use App\Domains\Shared\Services\AuditService;
use Illuminate\Support\Facades\DB;

class EngagementActionPlanService
{
    public function __construct(
        protected AuditService $audit
    ) {}

    public function createActionPlan(string $tenantId, array $data, ?int $userId = null): EngagementActionPlan
    {
        return DB::transaction(function () use ($tenantId, $data, $userId) {
            $plan = EngagementActionPlan::query()->create([
                'tenant_id' => $tenantId,
                'campaign_id' => $data['campaign_id'] ?? null,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'scope_type' => $data['scope_type'] ?? 'company',
                'scope_id' => $data['scope_id'] ?? null,
                'owner_id' => $data['owner_id'] ?? null,
                'due_date' => $data['due_date'] ?? null,
                'priority' => $data['priority'] ?? 'medium',
                'status' => 'open',
            ]);

            if (! empty($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $item) {
                    $this->addActionItem($plan, $item);
                }
            }

            EngagementActionPlanCreated::dispatch($plan);

            $this->audit->record(
                $tenantId,
                'EngagementActionPlanCreated',
                'create_action_plan',
                EngagementActionPlan::class,
                (string) $plan->id,
                $userId,
                null,
                ['title' => $plan->title, 'scope_type' => $plan->scope_type]
            );

            return $plan;
        });
    }

    public function updateActionPlan(EngagementActionPlan $plan, array $data, ?int $userId = null): EngagementActionPlan
    {
        return DB::transaction(function () use ($plan, $data, $userId) {
            $before = $plan->toArray();
            $plan->update([
                'title' => $data['title'] ?? $plan->title,
                'description' => $data['description'] ?? $plan->description,
                'owner_id' => $data['owner_id'] ?? $plan->owner_id,
                'due_date' => $data['due_date'] ?? $plan->due_date,
                'priority' => $data['priority'] ?? $plan->priority,
                'status' => $data['status'] ?? $plan->status,
            ]);

            $this->audit->record(
                (string) $plan->tenant_id,
                'EngagementActionPlanUpdated',
                'update_action_plan',
                EngagementActionPlan::class,
                (string) $plan->id,
                $userId,
                $before,
                $plan->fresh()->toArray()
            );

            return $plan->fresh();
        });
    }

    public function addActionItem(EngagementActionPlan $plan, array $data): EngagementActionItem
    {
        return EngagementActionItem::query()->create([
            'tenant_id' => $plan->tenant_id,
            'action_plan_id' => $plan->id,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'owner_id' => $data['owner_id'] ?? null,
            'due_date' => $data['due_date'] ?? null,
            'status' => $data['status'] ?? 'planned',
            'completion_percentage' => $data['completion_percentage'] ?? 0,
        ]);
    }

    public function updateActionItem(EngagementActionItem $item, array $data): EngagementActionItem
    {
        $item->update([
            'title' => $data['title'] ?? $item->title,
            'description' => $data['description'] ?? $item->description,
            'owner_id' => $data['owner_id'] ?? $item->owner_id,
            'due_date' => $data['due_date'] ?? $item->due_date,
            'status' => $data['status'] ?? $item->status,
            'completion_percentage' => $data['completion_percentage'] ?? $item->completion_percentage,
        ]);

        if ($item->status === 'completed' || (float) $item->completion_percentage >= 100) {
            EngagementActionItemCompleted::dispatch($item);
        }

        return $item->fresh();
    }
}
