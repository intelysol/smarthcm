<?php

namespace App\Domains\WorkforceAdmin\Services;

use App\Domains\Shared\Services\AuditService;
use App\Domains\WorkforceAdmin\Models\OpsQueue;
use App\Domains\WorkforceAdmin\Models\OpsQueueItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;

class OperationsQueueService
{
    public function __construct(
        protected AuditService $auditService
    ) {}

    /**
     * Enqueue a domain reference into an operational queue.
     */
    public function enqueueItem(OpsQueue $queue, array $data): OpsQueueItem
    {
        $itemNumber = 'QI-' . strtoupper(Str::random(8));

        $priority = $data['priority'] ?? $queue->default_priority ?? 'medium';
        $slaHours = $data['target_sla_hours'] ?? $queue->target_sla_hours ?? 24;
        $dueAt = now()->addHours($slaHours);

        $item = OpsQueueItem::create([
            'tenant_id' => $queue->tenant_id,
            'queue_id' => $queue->id,
            'item_number' => $itemNumber,
            'title' => $data['title'],
            'entity_type' => $data['entity_type'],
            'entity_id' => $data['entity_id'],
            'employee_id' => $data['employee_id'] ?? null,
            'priority' => $priority,
            'status' => 'pending',
            'assigned_to' => $data['assigned_to'] ?? null,
            'assigned_team' => $data['assigned_team'] ?? null,
            'due_at' => $dueAt,
            'payload' => $data['payload'] ?? null,
        ]);

        return $item;
    }

    /**
     * Assign queue item to an HR specialist or team.
     */
    public function assignItem(OpsQueueItem $item, ?User $user = null, ?string $team = null): OpsQueueItem
    {
        $item->update([
            'status' => 'assigned',
            'assigned_to' => $user?->id,
            'assigned_team' => $team,
            'first_responded_at' => $item->first_responded_at ?? now(),
        ]);

        return $item;
    }

    /**
     * Complete an operational queue item.
     */
    public function completeItem(OpsQueueItem $item, ?User $actor = null): OpsQueueItem
    {
        $item->update([
            'status' => 'completed',
            'resolved_at' => now(),
        ]);

        return $item;
    }
}
