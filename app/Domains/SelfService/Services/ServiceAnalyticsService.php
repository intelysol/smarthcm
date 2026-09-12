<?php

namespace App\Domains\SelfService\Services;

use App\Domains\SelfService\Enums\ServiceRequestStatus;
use App\Domains\SelfService\Enums\SlaStatus;
use App\Domains\SelfService\Models\HrKnowledgeArticle;
use App\Domains\SelfService\Models\HrKnowledgeFeedback;
use App\Domains\SelfService\Models\HrServiceRequest;
use App\Domains\SelfService\Models\HrServiceSlaInstance;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ServiceAnalyticsService
{
    public function getSummaryMetrics(string $tenantId): array
    {
        $totalRequests = HrServiceRequest::where('tenant_id', $tenantId)->count();
        $openRequests = HrServiceRequest::where('tenant_id', $tenantId)
            ->whereNotIn('status', [ServiceRequestStatus::RESOLVED->value, ServiceRequestStatus::CLOSED->value, ServiceRequestStatus::CANCELLED->value])
            ->count();
        $resolvedRequests = HrServiceRequest::where('tenant_id', $tenantId)
            ->whereIn('status', [ServiceRequestStatus::RESOLVED->value, ServiceRequestStatus::CLOSED->value])
            ->count();

        // SLA Compliance
        $totalSlaTracked = HrServiceSlaInstance::where('tenant_id', $tenantId)->count();
        $slaMetCount = HrServiceSlaInstance::where('tenant_id', $tenantId)->where('status', SlaStatus::MET->value)->count();
        $slaBreachedCount = HrServiceSlaInstance::where('tenant_id', $tenantId)->where('status', SlaStatus::BREACHED->value)->count();
        $slaComplianceRate = $totalSlaTracked > 0 ? round(($slaMetCount / $totalSlaTracked) * 100, 1) : 100.0;

        // Knowledge Base Satisfaction
        $helpfulVotes = HrKnowledgeFeedback::where('tenant_id', $tenantId)->where('is_helpful', true)->count();
        $totalVotes = HrKnowledgeFeedback::where('tenant_id', $tenantId)->count();
        $kbHelpfulnessRate = $totalVotes > 0 ? round(($helpfulVotes / $totalVotes) * 100, 1) : 100.0;

        return [
            'total_requests' => $totalRequests,
            'open_requests' => $openRequests,
            'resolved_requests' => $resolvedRequests,
            'sla_compliance_rate' => $slaComplianceRate,
            'sla_met_count' => $slaMetCount,
            'sla_breached_count' => $slaBreachedCount,
            'kb_helpfulness_rate' => $kbHelpfulnessRate,
        ];
    }

    public function getCategoryBreakdown(string $tenantId): array
    {
        return HrServiceRequest::where('hr_service_requests.tenant_id', $tenantId)
            ->join('hr_service_definitions', 'hr_service_definitions.id', '=', 'hr_service_requests.hr_service_definition_id')
            ->join('hr_service_categories', 'hr_service_categories.id', '=', 'hr_service_definitions.hr_service_category_id')
            ->selectRaw('hr_service_categories.name as category_name, hr_service_categories.code as category_code, COUNT(hr_service_requests.id) as request_count')
            ->groupBy('hr_service_categories.name', 'hr_service_categories.code')
            ->get()
            ->toArray();
    }
}
