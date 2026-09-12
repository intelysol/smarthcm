<?php

declare(strict_types=1);

namespace App\Domains\Compensation\Http\Controllers;

use App\Domains\Compensation\Models\CompensationCalibrationSession;
use App\Domains\Compensation\Models\CompensationCycle;
use App\Domains\Compensation\Models\CompensationRecommendation;
use App\Domains\Compensation\Services\CompensationCalibrationService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompensationCalibrationController extends Controller
{
    public function __construct(
        protected CompensationCalibrationService $calibrationService
    ) {}

    public function index(CompensationCycle $cycle): JsonResponse
    {
        $sessions = $cycle->calibrationSessions()->with(['records'])->get();

        return response()->json(['data' => $sessions]);
    }

    public function createSession(Request $request, CompensationCycle $cycle): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'scope_type' => 'nullable|string|max:50',
            'scope_id' => 'nullable|string|max:80',
            'session_date' => 'nullable|date',
            'facilitators' => 'nullable|array',
        ]);

        $session = $this->calibrationService->createSession(
            $request->user(),
            $cycle,
            $validated['title'],
            $validated['scope_type'] ?? 'department',
            $validated['scope_id'] ?? null,
            $validated['session_date'] ?? null,
            $validated['facilitators'] ?? []
        );

        return response()->json(['data' => $session], 201);
    }

    public function adjust(Request $request, CompensationCalibrationSession $session, CompensationRecommendation $recommendation): JsonResponse
    {
        $validated = $request->validate([
            'calibrated_percentage' => 'required|numeric|min:0|max:100',
            'mandatory_calibration_reason' => 'required|string|min:10',
        ]);

        $record = $this->calibrationService->recordAdjustment(
            $request->user(),
            $session,
            $recommendation,
            (float) $validated['calibrated_percentage'],
            $validated['mandatory_calibration_reason']
        );

        return response()->json(['data' => $record], 201);
    }

    public function complete(CompensationCalibrationSession $session): JsonResponse
    {
        $completed = $this->calibrationService->completeSession($session);

        return response()->json(['data' => $completed]);
    }
}
