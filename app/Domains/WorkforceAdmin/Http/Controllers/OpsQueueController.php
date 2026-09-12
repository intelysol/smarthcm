<?php

namespace App\Domains\WorkforceAdmin\Http\Controllers;

use App\Domains\WorkforceAdmin\Models\OpsQueue;
use App\Domains\WorkforceAdmin\Models\OpsQueueItem;
use App\Domains\WorkforceAdmin\Services\OperationsQueueService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OpsQueueController extends Controller
{
    public function __construct(
        protected OperationsQueueService $queueService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? $request->header('X-Tenant-ID');
        $queues = OpsQueue::when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->withCount(['items' => fn ($q) => $q->whereIn('status', ['pending', 'assigned', 'in_progress'])])
            ->latest()
            ->paginate(25);

        return response()->json($queues);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tenant_id' => 'sometimes|uuid',
            'code' => 'required|string|max:60',
            'name' => 'required|string|max:150',
            'category' => 'sometimes|string|max:60',
            'description' => 'nullable|string',
            'default_priority' => 'sometimes|string|in:critical,high,medium,low,informational',
            'target_sla_hours' => 'sometimes|integer|min:1',
        ]);

        $tenantId = $validated['tenant_id'] ?? $request->user()?->tenant_id ?? $request->header('X-Tenant-ID');

        $queue = OpsQueue::create(array_merge($validated, ['tenant_id' => $tenantId]));

        return response()->json($queue, 201);
    }

    public function items(OpsQueue $queue, Request $request): JsonResponse
    {
        $items = $queue->items()->with(['employee', 'assignee'])->latest()->paginate(25);
        return response()->json($items);
    }

    public function assignItem(OpsQueueItem $item, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'assigned_to' => 'nullable|uuid',
            'assigned_team' => 'nullable|string|max:80',
        ]);

        $assignee = !empty($validated['assigned_to']) ? \App\Models\User::find($validated['assigned_to']) : null;
        $updated = $this->queueService->assignItem($item, $assignee, $validated['assigned_team'] ?? null);

        return response()->json($updated);
    }

    public function completeItem(OpsQueueItem $item, Request $request): JsonResponse
    {
        $completed = $this->queueService->completeItem($item, $request->user());
        return response()->json($completed);
    }
}
