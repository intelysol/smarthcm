<?php

namespace App\Domains\Attendance\Http\Controllers;

use App\Domains\Attendance\Models\AttendanceSession;
use App\Domains\Attendance\Models\HcmLaborComplianceCheck;
use App\Domains\Attendance\Services\LaborComplianceIntelligenceService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LaborComplianceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? $request->input('tenant_id');
        $query = HcmLaborComplianceCheck::where('tenant_id', $tenantId);

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->input('employee_id'));
        }
        if ($request->filled('rule_code')) {
            $query->where('rule_code', $request->input('rule_code'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        return response()->json(['data' => $query->get()]);
    }

    public function scanSession(string $sessionId, LaborComplianceIntelligenceService $service): JsonResponse
    {
        $session = AttendanceSession::findOrFail($sessionId);
        $checks = $service->evaluateSessionCompliance($session);

        return response()->json(['message' => 'Compliance evaluated', 'checks_flagged' => $checks->count(), 'data' => $checks]);
    }

    public function waive(Request $request, string $id, LaborComplianceIntelligenceService $service): JsonResponse
    {
        $request->validate(['reason' => 'required|string']);
        $reviewerId = $request->user()?->id ?? 1;

        $check = $service->waiveComplianceCheck($id, $reviewerId, $request->input('reason'));

        return response()->json(['message' => 'Compliance check waived', 'data' => $check]);
    }
}