<?php

namespace App\Domains\WorkforceAdmin\Http\Controllers;

use App\Domains\WorkforceAdmin\Models\OpsException;
use App\Domains\WorkforceAdmin\Services\HrExceptionService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OpsExceptionController extends Controller
{
    public function __construct(
        protected HrExceptionService $exceptionService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? $request->header('X-Tenant-ID');
        $exceptions = OpsException::when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->with(['employee', 'owner'])
            ->latest()
            ->paginate(25);

        return response()->json($exceptions);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tenant_id' => 'sometimes|uuid',
            'exception_type' => 'required|string|max:80',
            'severity' => 'sometimes|string|in:critical,high,medium,low,informational',
            'domain' => 'required|string|max:60',
            'entity_type' => 'required|string|max:100',
            'entity_id' => 'required|uuid',
            'employee_id' => 'nullable|uuid',
            'description' => 'required|string',
            'resolution_guidance' => 'nullable|string',
        ]);

        $tenantId = $validated['tenant_id'] ?? $request->user()?->tenant_id ?? $request->header('X-Tenant-ID');

        $exception = $this->exceptionService->recordException(array_merge($validated, ['tenant_id' => $tenantId]));

        return response()->json($exception, 201);
    }

    public function show(OpsException $exception): JsonResponse
    {
        return response()->json($exception->load(['employee', 'owner', 'assignments.assignee', 'events']));
    }

    public function assign(OpsException $exception, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'assigned_user_id' => 'nullable|uuid',
            'assigned_team' => 'nullable|string|max:80',
        ]);

        $assignee = !empty($validated['assigned_user_id']) ? \App\Models\User::find($validated['assigned_user_id']) : null;
        $updated = $this->exceptionService->assignException($exception, $assignee, $validated['assigned_team'] ?? null, $request->user());

        return response()->json($updated);
    }

    public function resolve(OpsException $exception, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'resolution_notes' => 'required|string',
        ]);

        $resolved = $this->exceptionService->resolveException($exception, $validated['resolution_notes'], $request->user());

        return response()->json($resolved);
    }
}
