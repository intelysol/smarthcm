<?php

declare(strict_types=1);

namespace App\Domains\Performance\Http\Controllers;

use App\Domains\Performance\Models\PerformanceImprovementPlan;
use App\Domains\Performance\Services\PerformanceImprovementPlanService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PerformancePipController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = PerformanceImprovementPlan::query()
            ->when($request->query('employee_id'), fn ($q, $id) => $q->where('employee_id', $id))
            ->when($request->query('cycle_id'), fn ($q, $id) => $q->where('cycle_id', $id))
            ->with('actions');

        return response()->json($query->paginate((int) $request->integer('per_page', 25)));
    }

    public function store(Request $request, PerformanceImprovementPlanService $service): JsonResponse
    {
        $data = $request->validate([
            'tenant_id' => ['required', 'string'],
            'employee_id' => ['required', 'string'],
            'cycle_id' => ['nullable', 'string'],
            'summary' => ['required', 'string'],
            'details' => ['nullable', 'array'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'actions' => ['nullable', 'array'],
        ]);

        $pip = $service->createPip($data, $request->user());
        return response()->json(['data' => $pip], 201);
    }

    public function show(PerformanceImprovementPlan $pip): JsonResponse
    {
        return response()->json(['data' => $pip->load('actions')]);
    }

    public function addAction(Request $request, PerformanceImprovementPlan $pip, PerformanceImprovementPlanService $service): JsonResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'owner_id' => ['nullable', 'string'],
            'due_date' => ['nullable', 'date'],
            'status' => ['nullable', 'string'],
            'completion_percentage' => ['nullable', 'numeric'],
        ]);

        $action = $service->addAction($pip, $data);
        return response()->json(['data' => $action], 201);
    }

    public function conclude(Request $request, PerformanceImprovementPlan $pip, PerformanceImprovementPlanService $service): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', 'string'], // successful, extended, unsuccessful
            'conclusion_notes' => ['nullable', 'string'],
        ]);

        $concluded = $service->concludePip($pip, $data['status'], $data['conclusion_notes'] ?? null);
        return response()->json(['data' => $concluded]);
    }
}