<?php

namespace App\Domains\WorkforceAdmin\Http\Controllers;

use App\Domains\WorkforceAdmin\Models\OpsChecklistInstance;
use App\Domains\WorkforceAdmin\Models\OpsChecklistItem;
use App\Domains\WorkforceAdmin\Models\OpsChecklistTemplate;
use App\Domains\WorkforceAdmin\Services\HrChecklistService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OpsChecklistController extends Controller
{
    public function __construct(
        protected HrChecklistService $checklistService
    ) {}

    public function templates(Request $request): JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? $request->header('X-Tenant-ID');
        $templates = OpsChecklistTemplate::when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->where('is_active', true)
            ->latest()
            ->paginate(25);

        return response()->json($templates);
    }

    public function createTemplate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tenant_id' => 'sometimes|uuid',
            'code' => 'required|string|max:60',
            'name' => 'required|string|max:150',
            'trigger_type' => 'required|string|max:60',
            'default_items' => 'required|array',
        ]);

        $tenantId = $validated['tenant_id'] ?? $request->user()?->tenant_id ?? $request->header('X-Tenant-ID');
        $template = OpsChecklistTemplate::create(array_merge($validated, ['tenant_id' => $tenantId]));

        return response()->json($template, 201);
    }

    public function instances(Request $request): JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? $request->header('X-Tenant-ID');
        $instances = OpsChecklistInstance::when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->with(['template', 'employee', 'items'])
            ->latest()
            ->paginate(25);

        return response()->json($instances);
    }

    public function instantiate(OpsChecklistTemplate $template, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|uuid',
            'target_completion_date' => 'nullable|date',
        ]);

        $instance = $this->checklistService->instantiateChecklist(
            $template,
            $validated['employee_id'],
            $validated['target_completion_date'] ?? null,
            $request->user()
        );

        return response()->json($instance->load('items'), 201);
    }

    public function completeItem(OpsChecklistItem $item, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'sometimes|string|in:completed,waived',
        ]);

        $updated = $this->checklistService->completeItem($item, $request->user(), $validated['status'] ?? 'completed');
        return response()->json($updated);
    }
}
