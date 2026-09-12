<?php

namespace App\Domains\WorkforceCost\Http\Controllers;

use App\Domains\WorkforceCost\Services\AdvisoryWorkforceCostAiService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AdvisoryWorkforceCostAiController extends Controller
{
    public function insights(Request $request, AdvisoryWorkforceCostAiService $service): JsonResponse
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $tenantId = $request->user()?->tenant_id ?? $request->input('tenant_id');
        $insights = $service->generateCostInsights(
            $tenantId,
            Carbon::parse($validated['start_date']),
            Carbon::parse($validated['end_date'])
        );

        return response()->json($insights);
    }
}