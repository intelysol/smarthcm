<?php

namespace App\Domains\SelfService\Http\Controllers;

use App\Domains\Employee\Models\Employee;
use App\Domains\SelfService\Models\HrServiceRequest;
use App\Domains\SelfService\Services\ServiceFeedbackService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceFeedbackController extends Controller
{
    public function __construct(
        protected ServiceFeedbackService $feedbackService
    ) {}

    public function submit(HrServiceRequest $request, Request $httpRequest): JsonResponse
    {
        $validated = $httpRequest->validate([
            'rating' => 'required|integer|min:1|max:5',
            'satisfaction_level' => 'sometimes|string|in:very_satisfied,satisfied,neutral,dissatisfied,very_dissatisfied',
            'comments' => 'nullable|string|max:1000',
            'timeliness_rating' => 'nullable|integer|min:1|max:5',
            'knowledge_rating' => 'nullable|integer|min:1|max:5',
            'helpfulness_rating' => 'nullable|integer|min:1|max:5',
        ]);

        $employee = Employee::where('tenant_id', $request->tenant_id)
            ->where('user_id', $httpRequest->user()?->id)
            ->first() ?? $request->employee;

        $feedback = $this->feedbackService->submitFeedback($request, $employee, $validated);

        return response()->json($feedback, 201);
    }

    public function summary(Request $httpRequest): JsonResponse
    {
        $tenantId = $httpRequest->user()?->tenant_id ?? $httpRequest->header('X-Tenant-ID') ?? 'default';
        $summary = $this->feedbackService->getFeedbackSummary($tenantId);

        return response()->json($summary);
    }
}
