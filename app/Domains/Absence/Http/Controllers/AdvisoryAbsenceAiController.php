<?php

namespace App\Domains\Absence\Http\Controllers;

use App\Domains\Absence\Services\AdvisoryAbsenceAiService;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdvisoryAbsenceAiController extends Controller
{
    public function insights(Request $request, AdvisoryAbsenceAiService $service): JsonResponse
    {
        $validated = $request->validate([
            'period_start' => 'required|date',
            'period_end' => 'required|date',
        ]);

        $tenantId = $request->user()?->tenant_id ?? $request->input('tenant_id');

        $insights = $service->summarizeAbsenceTrends(
            $tenantId,
            Carbon::parse($validated['period_start']),
            Carbon::parse($validated['period_end'])
        );

        return response()->json($insights);
    }
}