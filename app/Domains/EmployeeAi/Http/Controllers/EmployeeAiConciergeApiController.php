<?php

namespace App\Domains\EmployeeAi\Http\Controllers;

use App\Domains\EmployeeAi\Contracts\EmployeeAiConciergeInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeAiConciergeApiController extends Controller
{
    public function __construct(
        protected EmployeeAiConciergeInterface $conciergeService
    ) {}

    public function startSession(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID', 'default-tenant');
        $userId = $request->header('X-User-ID', 'default-user');
        $employeeId = $request->header('X-Employee-ID');
        $persona = $request->input('persona', 'EMPLOYEE');

        $session = $this->conciergeService->startSession($tenantId, $userId, $employeeId, $persona);
        return response()->json(['session' => $session->toArray()]);
    }

    public function chat(Request $request, string $sessionId): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID', 'default-tenant');
        $userId = $request->header('X-User-ID', 'default-user');
        $employeeId = $request->header('X-Employee-ID');
        $prompt = $request->input('prompt', '');

        $result = $this->conciergeService->chat($sessionId, $prompt, $tenantId, $userId, $employeeId);
        return response()->json($result);
    }

    public function confirmAction(Request $request, string $actionId): JsonResponse
    {
        $userId = $request->header('X-User-ID', 'default-user');
        $comment = $request->input('comment');

        $action = $this->conciergeService->confirmAction($actionId, $userId, $comment);
        return response()->json(['action' => $action->toArray()]);
    }

    public function mySummary(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID', 'default-tenant');
        $employeeId = $request->header('X-Employee-ID');

        if (!$employeeId) {
            return response()->json(['error' => 'Employee identity context required'], 400);
        }

        $summary = $this->conciergeService->getMyHrSummary($tenantId, $employeeId);
        return response()->json(['summary' => $summary]);
    }

    public function suggestions(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID', 'default-tenant');
        $employeeId = $request->header('X-Employee-ID', 'default-emp');

        $suggestions = $this->conciergeService->getProactiveSuggestions($tenantId, $employeeId);
        return response()->json(['suggestions' => $suggestions]);
    }
}
