<?php

declare(strict_types=1);

namespace App\Domains\Performance\Http\Controllers;

use App\Domains\Performance\Models\PerformanceFeedbackRequest;
use App\Domains\Performance\Services\PerformanceFeedbackService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PerformanceFeedbackController extends Controller
{
    public function index(Request $request, PerformanceFeedbackService $service): JsonResponse
    {
        $employeeId = (string) $request->input('employee_id');
        $cycleId = (string) $request->input('cycle_id');

        $feedback = $service->getFeedbackForEmployee($employeeId, $cycleId, $request->user());
        return response()->json(['data' => $feedback]);
    }

    public function requestFeedback(Request $request, PerformanceFeedbackService $service): JsonResponse
    {
        $data = $request->validate([
            'tenant_id' => ['required', 'string'],
            'cycle_id' => ['required', 'string'],
            'employee_id' => ['required', 'string'],
            'requested_from_employee_id' => ['required', 'string'],
            'relationship_type' => ['required', 'string'],
            'feedback_identity_hidden' => ['nullable', 'boolean'],
            'due_at' => ['nullable', 'date'],
        ]);

        $feedbackRequest = $service->requestFeedback($data, $request->user());
        return response()->json(['data' => $feedbackRequest], 201);
    }

    public function submitResponse(Request $request, PerformanceFeedbackRequest $feedbackRequest, PerformanceFeedbackService $service): JsonResponse
    {
        $data = $request->validate([
            'rating' => ['nullable', 'numeric'],
            'response' => ['required', 'string'],
        ]);

        $feedbackResponse = $service->submitFeedback($feedbackRequest, $data, $request->user());
        return response()->json(['data' => $feedbackResponse], 201);
    }
}
