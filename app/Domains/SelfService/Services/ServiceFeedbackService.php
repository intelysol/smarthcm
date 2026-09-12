<?php

namespace App\Domains\SelfService\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\SelfService\Models\HrServiceFeedback;
use App\Domains\SelfService\Models\HrServiceRequest;

class ServiceFeedbackService
{
    /**
     * Record CSAT feedback for a service request.
     */
    public function submitFeedback(HrServiceRequest $request, Employee $employee, array $data): HrServiceFeedback
    {
        return HrServiceFeedback::create([
            'tenant_id' => $request->tenant_id,
            'hr_service_request_id' => $request->id,
            'employee_id' => $employee->id,
            'rating' => (int) $data['rating'],
            'satisfaction_level' => $data['satisfaction_level'] ?? ($data['rating'] >= 4 ? 'satisfied' : ($data['rating'] == 3 ? 'neutral' : 'dissatisfied')),
            'comments' => $data['comments'] ?? null,
            'timeliness_rating' => $data['timeliness_rating'] ?? $data['rating'],
            'knowledge_rating' => $data['knowledge_rating'] ?? $data['rating'],
            'helpfulness_rating' => $data['helpfulness_rating'] ?? $data['rating'],
        ]);
    }

    /**
     * Get aggregate CSAT and satisfaction metrics for a tenant.
     */
    public function getFeedbackSummary(string $tenantId): array
    {
        $feedbacks = HrServiceFeedback::where('tenant_id', $tenantId)->get();
        $totalResponses = $feedbacks->count();

        if ($totalResponses === 0) {
            return [
                'total_responses' => 0,
                'average_csat' => 5.0,
                'satisfaction_rate' => 100.0,
                'breakdown' => [
                    '5_star' => 0,
                    '4_star' => 0,
                    '3_star' => 0,
                    '2_star' => 0,
                    '1_star' => 0,
                ],
            ];
        }

        $avgScore = round($feedbacks->avg('rating'), 2);
        $satisfiedCount = $feedbacks->where('rating', '>=', 4)->count();
        $satisfactionRate = round(($satisfiedCount / $totalResponses) * 100, 1);

        return [
            'total_responses' => $totalResponses,
            'average_csat' => $avgScore,
            'satisfaction_rate' => $satisfactionRate,
            'avg_timeliness' => round($feedbacks->avg('timeliness_rating'), 2),
            'avg_knowledge' => round($feedbacks->avg('knowledge_rating'), 2),
            'avg_helpfulness' => round($feedbacks->avg('helpfulness_rating'), 2),
            'breakdown' => [
                '5_star' => $feedbacks->where('rating', 5)->count(),
                '4_star' => $feedbacks->where('rating', 4)->count(),
                '3_star' => $feedbacks->where('rating', 3)->count(),
                '2_star' => $feedbacks->where('rating', 2)->count(),
                '1_star' => $feedbacks->where('rating', 1)->count(),
            ],
        ];
    }
}
