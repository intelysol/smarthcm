<?php

namespace App\Domains\Attendance\Http\Controllers;

use App\Domains\Attendance\Services\AdvisoryTimeAiService;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdvisoryTimeAiController extends Controller
{
    public function anomalies(Request $request, AdvisoryTimeAiService $service): JsonResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|uuid',
            'from' => 'required|date',
            'to' => 'required|date',
        ]);

        $analysis = $service->analyzeAttendanceAnomalies(
            $validated['employee_id'],
            Carbon::parse($validated['from']),
            Carbon::parse($validated['to'])
        );

        return response()->json($analysis);
    }

    public function timesheetVariance(string $timesheetId, AdvisoryTimeAiService $service): JsonResponse
    {
        $explanation = $service->explainTimesheetVariance($timesheetId);

        return response()->json($explanation);
    }
}