<?php

declare(strict_types=1);

namespace App\Domains\Performance\Http\Controllers;

use App\Domains\Performance\Services\PerformanceAiAdvisoryService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PerformanceAiController extends Controller
{
    public function draftSmartGoal(Request $request, PerformanceAiAdvisoryService $service): JsonResponse
    {
        $data = $request->validate([
            'description' => ['required', 'string'],
        ]);

        $result = $service->draftSmartGoal($data['description']);
        return response()->json(['data' => $result]);
    }

    public function summarize(Request $request, PerformanceAiAdvisoryService $service): JsonResponse
    {
        $data = $request->validate([
            'goals' => ['required', 'array'],
            'competencies' => ['nullable', 'array'],
            'feedback' => ['nullable', 'array'],
        ]);

        $result = $service->summarizePerformance(
            $data['goals'],
            $data['competencies'] ?? [],
            $data['feedback'] ?? []
        );

        return response()->json(['data' => $result]);
    }
}
