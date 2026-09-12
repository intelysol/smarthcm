<?php

namespace App\Domains\EmployeeProfile\Http\Controllers;

use App\Domains\Employee\Models\Employee;
use App\Domains\EmployeeProfile\Services\EmployeeProfileAiService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeProfileAiController extends Controller
{
    public function __construct(protected EmployeeProfileAiService $aiService)
    {
    }

    public function nlSearch(Request $request): JsonResponse
    {
        $query = (string) $request->input('query', '');
        $parsed = $this->aiService->parseNaturalLanguageSearch($query);

        return response()->json([
            'success' => true,
            'data' => $parsed,
        ]);
    }

    public function generateSummary(Request $request, string $employeeId): JsonResponse
    {
        $employee = Employee::findOrFail($employeeId);
        $summary = $this->aiService->generateProfileSummary($employee);

        return response()->json([
            'success' => true,
            'data' => $summary,
        ]);
    }

    public function inquire(Request $request): JsonResponse
    {
        $inquiry = (string) $request->input('inquiry', '');
        $result = $this->aiService->processAiInquiry($request->user()->tenant_id, $inquiry);

        return response()->json([
            'success' => $result['status'] === 'success',
            'data' => $result,
        ]);
    }
}
