<?php

namespace App\Domains\WorkforceIntelligence\Services;

use App\Domains\WorkforceIntelligence\Models\CommandCenterDecisionItem;
use Carbon\Carbon;
use Illuminate\Support\Str;

class WorkforceDecisionQueueService
{
    public function submitDecisionItem(array $data): CommandCenterDecisionItem
    {
        return CommandCenterDecisionItem::create([
            'tenant_id' => $data['tenant_id'],
            'department_id' => $data['department_id'] ?? null,
            'decision_code' => $data['decision_code'] ?? 'DEC-' . Str::upper(Str::random(6)),
            'source_domain' => $data['source_domain'] ?? 'OPTIMIZATION',
            'source_reference_id' => $data['source_reference_id'] ?? null,
            'title' => $data['title'],
            'summary' => $data['summary'],
            'urgency' => $data['urgency'] ?? 'NORMAL',
            'estimated_cost_impact' => $data['estimated_cost_impact'] ?? 0.0,
            'estimated_capacity_impact' => $data['estimated_capacity_impact'] ?? 0.0,
            'status' => 'PENDING',
            'assigned_approver_id' => $data['assigned_approver_id'] ?? null,
            'deadline_at' => $data['deadline_at'] ?? Carbon::now()->addDays(5),
            'payload' => $data['payload'] ?? [],
        ]);
    }

    public function processDecision(string $decisionId, string $status, ?string $userId, ?string $notes = null): CommandCenterDecisionItem
    {
        $item = CommandCenterDecisionItem::findOrFail($decisionId);
        $item->update([
            'status' => $status,
            'actioned_by_user_id' => $userId,
            'action_notes' => $notes,
            'actioned_at' => Carbon::now(),
        ]);

        return $item;
    }

    public function getPendingDecisions(string $tenantId, ?string $departmentId = null): array
    {
        $q = CommandCenterDecisionItem::where('tenant_id', $tenantId)->where('status', 'PENDING');
        if ($departmentId) {
            $q->where('department_id', $departmentId);
        }

        return $q->orderBy('deadline_at', 'asc')->get()->toArray();
    }
}
