<?php

namespace App\Domains\WorkforceAdmin\Services;

use App\Domains\Shared\Services\AuditService;
use App\Domains\WorkforceAdmin\Models\OpsChecklistInstance;
use App\Domains\WorkforceAdmin\Models\OpsChecklistItem;
use App\Domains\WorkforceAdmin\Models\OpsChecklistTemplate;
use App\Models\User;
use Illuminate\Support\Str;

class HrChecklistService
{
    public function __construct(
        protected AuditService $auditService
    ) {}

    /**
     * Instantiate a checklist for an employee from a template.
     */
    public function instantiateChecklist(OpsChecklistTemplate $template, string $employeeId, ?string $targetCompletionDate = null, ?User $actor = null): OpsChecklistInstance
    {
        $refNumber = 'CHK-' . strtoupper(Str::random(8));

        $instance = OpsChecklistInstance::create([
            'tenant_id' => $template->tenant_id,
            'template_id' => $template->id,
            'employee_id' => $employeeId,
            'reference_number' => $refNumber,
            'status' => 'open',
            'target_completion_date' => $targetCompletionDate ?? now()->addDays(14)->toDateString(),
        ]);

        $defaultItems = $template->default_items ?? [];
        foreach ($defaultItems as $itemDef) {
            OpsChecklistItem::create([
                'tenant_id' => $template->tenant_id,
                'checklist_instance_id' => $instance->id,
                'category' => $itemDef['category'] ?? 'general',
                'title' => $itemDef['title'] ?? 'Task Item',
                'status' => 'pending',
                'assigned_to' => $itemDef['assigned_to'] ?? null,
            ]);
        }

        $this->auditService->record(
            tenantId: $instance->tenant_id,
            eventType: 'checklist_instantiated',
            action: 'create',
            entityType: 'OpsChecklistInstance',
            entityId: $instance->id,
            actorId: $actor?->id ? (int) $actor->id : null,
            before: null,
            after: $instance->toArray()
        );

        return $instance;
    }

    /**
     * Complete a checklist item.
     */
    public function completeItem(OpsChecklistItem $item, User $actor, string $status = 'completed'): OpsChecklistItem
    {
        $item->update([
            'status' => $status,
            'completed_at' => now(),
            'completed_by' => $actor->id,
        ]);

        // Check if all items in instance are completed/waived
        $instance = $item->checklistInstance;
        $pendingCount = $instance->items()->where('status', 'pending')->count();

        if ($pendingCount === 0) {
            $instance->update([
                'status' => 'completed',
                'actual_completion_date' => now()->toDateString(),
            ]);
        } elseif ($instance->status === 'open') {
            $instance->update(['status' => 'in_progress']);
        }

        return $item;
    }
}
