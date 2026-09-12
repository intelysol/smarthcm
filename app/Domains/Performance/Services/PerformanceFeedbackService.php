<?php

declare(strict_types=1);

namespace App\Domains\Performance\Services;

use App\Domains\Performance\Events\PerformanceFeedbackRequested;
use App\Domains\Performance\Events\PerformanceFeedbackSubmitted;
use App\Domains\Performance\Models\PerformanceCycle;
use App\Domains\Performance\Models\PerformanceFeedbackRequest;
use App\Domains\Performance\Models\PerformanceFeedbackResponse;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PerformanceFeedbackService
{
    /**
     * Request 360 multi-rater feedback for an employee.
     */
    public function requestFeedback(array $data, ?User $actor = null): PerformanceFeedbackRequest
    {
        return DB::transaction(function () use ($data, $actor) {
            $request = PerformanceFeedbackRequest::create([
                'tenant_id' => $data['tenant_id'],
                'cycle_id' => $data['cycle_id'],
                'employee_id' => $data['employee_id'],
                'requested_from_employee_id' => $data['requested_from_employee_id'],
                'requested_by' => $actor?->id,
                'relationship_type' => $data['relationship_type'] ?? 'peer', // peer, direct_report, manager, customer
                'feedback_identity_hidden' => (bool) ($data['feedback_identity_hidden'] ?? true),
                'due_at' => $data['due_at'] ?? Carbon::now()->addDays(14),
                'status' => 'pending',
            ]);

            event(new PerformanceFeedbackRequested($request));

            return $request;
        });
    }

    /**
     * Submit feedback response.
     */
    public function submitFeedback(
        PerformanceFeedbackRequest $request,
        array $data,
        ?User $actor = null
    ): PerformanceFeedbackResponse {
        if ($request->status === 'completed') {
            throw ValidationException::withMessages([
                'request' => ['Feedback has already been submitted for this request.'],
            ]);
        }

        return DB::transaction(function () use ($request, $data) {
            $response = PerformanceFeedbackResponse::create([
                'tenant_id' => $request->tenant_id,
                'request_id' => $request->id,
                'rating' => $data['rating'] ?? null,
                'response' => $data['response'],
                'submitted_at' => Carbon::now(),
            ]);

            $request->update(['status' => 'completed']);

            event(new PerformanceFeedbackSubmitted($response));

            return $response;
        });
    }

    /**
     * Get aggregated feedback for an employee, strictly applying anonymity threshold when requested.
     */
    public function getFeedbackForEmployee(
        string $employeeId,
        string $cycleId,
        User $viewer,
        int $anonymityThreshold = 3
    ): Collection {
        $responses = PerformanceFeedbackResponse::whereHas('request', function ($query) use ($employeeId, $cycleId) {
            $query->where('employee_id', $employeeId)->where('cycle_id', $cycleId);
        })->with('request')->get();

        $security = app(PerformanceSecurityService::class);
        return $security->filterFeedbackResponsesForViewer($responses, $viewer, $anonymityThreshold);
    }
}
