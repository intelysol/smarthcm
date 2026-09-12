<?php

declare(strict_types=1);

namespace App\Domains\Performance\Http\Controllers;

use App\Domains\Performance\Models\PerformanceCalibrationSession;
use App\Domains\Performance\Services\PerformanceCalibrationService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PerformanceCalibrationController extends Controller
{
    public function createSession(Request $request, PerformanceCalibrationService $service): JsonResponse
    {
        $data = $request->validate([
            'tenant_id' => ['required', 'string'],
            'cycle_id' => ['required', 'string'],
            'name' => ['required', 'string'],
        ]);

        $session = $service->createSession($data, $request->user());
        return response()->json(['data' => $session], 201);
    }

    public function adjustRating(Request $request, PerformanceCalibrationSession $session, PerformanceCalibrationService $service): JsonResponse
    {
        $data = $request->validate([
            'employee_id' => ['required', 'string'],
            'final_rating' => ['required', 'numeric'],
            'change_reason' => ['required', 'string'],
        ]);

        $record = $service->adjustRating($session, $data['employee_id'], (float) $data['final_rating'], $data['change_reason'], $request->user());
        return response()->json(['data' => $record]);
    }

    public function finalize(Request $request, PerformanceCalibrationSession $session, PerformanceCalibrationService $service): JsonResponse
    {
        $session = $service->finalizeSession($session, $request->user());
        return response()->json(['data' => $session]);
    }
}
