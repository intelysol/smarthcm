<?php

namespace App\Domains\Lifecycle\Http\Controllers;

use App\Domains\Lifecycle\Models\PersonnelActionBulkBatch;
use App\Domains\Lifecycle\Models\PersonnelActionType;
use App\Domains\Lifecycle\Services\PersonnelActionBulkService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PersonnelActionBulkController extends Controller
{
    public function __construct(protected PersonnelActionBulkService $bulkService)
    {
    }

    public function createBatch(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'action_type_id' => 'required|uuid',
            'name' => 'required|string|max:150',
            'effective_date' => 'nullable|date',
            'items' => 'required|array|min:1',
            'items.*.employee_id' => 'required|uuid',
            'items.*.payload' => 'required|array',
        ]);

        $actionType = PersonnelActionType::findOrFail($validated['action_type_id']);
        $batch = $this->bulkService->createBatch(
            $request->user(),
            $actionType,
            $validated['name'],
            $validated['items'],
            $validated['effective_date'] ?? null
        );

        return response()->json($batch, 201);
    }

    public function dryRun(string $id): JsonResponse
    {
        $batch = PersonnelActionBulkBatch::with('items')->findOrFail($id);
        $validatedBatch = $this->bulkService->validateDryRun($batch);

        return response()->json($validatedBatch);
    }

    public function execute(Request $request, string $id): JsonResponse
    {
        $batch = PersonnelActionBulkBatch::findOrFail($id);
        $executedBatch = $this->bulkService->executeBatch($batch, $request->user());

        return response()->json($executedBatch);
    }
}
