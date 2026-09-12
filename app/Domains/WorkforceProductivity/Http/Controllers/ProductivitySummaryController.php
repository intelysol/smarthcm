<?php

namespace App\Domains\WorkforceProductivity\Http\Controllers;

use App\Domains\WorkforceProductivity\Models\HcmProductivityMeasurement;
use App\Domains\WorkforceProductivity\Models\HcmProductivityScorecard;
use App\Domains\WorkforceProductivity\Models\HcmProductivitySnapshot;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductivitySummaryController extends Controller
{
    public function summary(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID') ?? $request->query('tenant_id');
        if (! $tenantId) {
            return response()->json(['error' => 'Tenant ID is required'], 400);
        }

        $latestSnapshot = HcmProductivitySnapshot::where('tenant_id', $tenantId)
            ->latest('period_end')
            ->first();

        $recentMeasurements = HcmProductivityMeasurement::with(['metricDefinition', 'department'])
            ->where('tenant_id', $tenantId)
            ->latest('period_end')
            ->take(10)
            ->get();

        $scorecards = HcmProductivityScorecard::where('tenant_id', $tenantId)
            ->latest('period_end')
            ->take(5)
            ->get();

        return response()->json([
            'snapshot' => $latestSnapshot,
            'recent_measurements' => $recentMeasurements,
            'scorecards' => $scorecards,
        ]);
    }
}
