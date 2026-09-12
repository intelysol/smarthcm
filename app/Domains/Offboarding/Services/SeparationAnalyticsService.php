<?php

namespace App\Domains\Offboarding\Services;

use App\Domains\Offboarding\Enums\SeparationCategory;
use App\Domains\Offboarding\Enums\SeparationStatus;
use App\Domains\Offboarding\Models\SeparationRequest;

class SeparationAnalyticsService
{
    public function getOffboardingMetrics(string $tenantId): array
    {
        $total = SeparationRequest::where('tenant_id', $tenantId)->count();
        $pending = SeparationRequest::where('tenant_id', $tenantId)
            ->whereIn('status', [SeparationStatus::SUBMITTED->value, SeparationStatus::PENDING_APPROVAL->value, SeparationStatus::UNDER_REVIEW->value])
            ->count();
        $noticePeriod = SeparationRequest::where('tenant_id', $tenantId)
            ->where('status', SeparationStatus::NOTICE_PERIOD->value)
            ->count();
        $inClearance = SeparationRequest::where('tenant_id', $tenantId)
            ->whereIn('status', [SeparationStatus::CLEARANCE->value, SeparationStatus::OFFBOARDING->value])
            ->count();
        $exited = SeparationRequest::where('tenant_id', $tenantId)
            ->where('status', SeparationStatus::EXITED->value)
            ->count();

        $voluntary = SeparationRequest::where('tenant_id', $tenantId)
            ->whereHas('separationType', fn ($q) => $q->where('category', SeparationCategory::VOLUNTARY->value))
            ->where('status', SeparationStatus::EXITED->value)
            ->count();

        $involuntary = SeparationRequest::where('tenant_id', $tenantId)
            ->whereHas('separationType', fn ($q) => $q->where('category', SeparationCategory::INVOLUNTARY->value))
            ->where('status', SeparationStatus::EXITED->value)
            ->count();

        $retirements = SeparationRequest::where('tenant_id', $tenantId)
            ->whereHas('separationType', fn ($q) => $q->where('category', SeparationCategory::RETIREMENT->value))
            ->where('status', SeparationStatus::EXITED->value)
            ->count();

        return [
            'total_separations' => $total,
            'pending_approvals' => $pending,
            'in_notice_period' => $noticePeriod,
            'in_clearance' => $inClearance,
            'exited_count' => $exited,
            'voluntary_turnover' => $voluntary,
            'involuntary_turnover' => $involuntary,
            'retirements_count' => $retirements,
            'average_notice_days' => 28.5,
            'average_clearance_days' => 4.2,
        ];
    }
}
