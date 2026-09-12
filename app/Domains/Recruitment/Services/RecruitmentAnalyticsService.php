<?php

namespace App\Domains\Recruitment\Services;

use App\Domains\Recruitment\Enums\ApplicationStatus;
use App\Domains\Recruitment\Models\HcmRecruitmentApplication;
use App\Domains\Recruitment\Models\HcmRecruitmentCandidate;
use App\Domains\Recruitment\Models\HcmRecruitmentOffer;
use App\Domains\Recruitment\Models\HcmRecruitmentRequisition;
use App\Domains\Recruitment\Models\HcmRecruitmentSource;

class RecruitmentAnalyticsService
{
    public function getRecruitmentFunnel(string $tenantId, ?string $requisitionId = null): array
    {
        $query = HcmRecruitmentApplication::where('tenant_id', $tenantId);
        if ($requisitionId) {
            $query->where('requisition_id', $requisitionId);
        }

        $totalApplications = (clone $query)->count();
        $screened = (clone $query)->whereIn('status', [ApplicationStatus::SHORTLISTED->value, ApplicationStatus::INTERVIEW->value, ApplicationStatus::ASSESSMENT->value, ApplicationStatus::OFFER->value, ApplicationStatus::HIRED->value])->count();
        $interviewed = (clone $query)->whereIn('status', [ApplicationStatus::INTERVIEW->value, ApplicationStatus::ASSESSMENT->value, ApplicationStatus::OFFER->value, ApplicationStatus::HIRED->value])->count();
        $offered = (clone $query)->whereIn('status', [ApplicationStatus::OFFER->value, ApplicationStatus::HIRED->value])->count();
        $hired = (clone $query)->where('status', ApplicationStatus::HIRED->value)->count();

        return [
            'total_applications' => $totalApplications,
            'screened' => $screened,
            'screened_conversion_pct' => $totalApplications > 0 ? round(($screened / $totalApplications) * 100, 1) : 0,
            'interviewed' => $interviewed,
            'interview_conversion_pct' => $screened > 0 ? round(($interviewed / $screened) * 100, 1) : 0,
            'offered' => $offered,
            'offer_conversion_pct' => $interviewed > 0 ? round(($offered / $interviewed) * 100, 1) : 0,
            'hired' => $hired,
            'hire_conversion_pct' => $offered > 0 ? round(($hired / $offered) * 100, 1) : 0,
            'overall_yield_pct' => $totalApplications > 0 ? round(($hired / $totalApplications) * 100, 1) : 0,
        ];
    }

    public function getRecruitmentKpis(string $tenantId): array
    {
        $openRequisitions = HcmRecruitmentRequisition::where('tenant_id', $tenantId)->whereIn('status', ['open', 'hiring'])->count();
        $totalCandidates = HcmRecruitmentCandidate::where('tenant_id', $tenantId)->count();
        $activeApplications = HcmRecruitmentApplication::where('tenant_id', $tenantId)->whereNotIn('status', ['hired', 'rejected', 'withdrawn'])->count();
        $pendingOffers = HcmRecruitmentOffer::where('tenant_id', $tenantId)->whereIn('status', ['approved', 'sent'])->count();

        // Calculate Average Time to Hire
        $hiredApps = HcmRecruitmentApplication::where('tenant_id', $tenantId)
            ->where('status', ApplicationStatus::HIRED->value)
            ->whereNotNull('hired_at')
            ->get();

        $avgDaysToHire = 0;
        if ($hiredApps->count() > 0) {
            $totalDays = 0;
            foreach ($hiredApps as $app) {
                $totalDays += $app->applied_at->diffInDays($app->hired_at);
            }
            $avgDaysToHire = round($totalDays / $hiredApps->count(), 1);
        }

        return [
            'open_requisitions' => $openRequisitions,
            'total_candidates' => $totalCandidates,
            'active_applications' => $activeApplications,
            'pending_offers' => $pendingOffers,
            'average_time_to_hire_days' => $avgDaysToHire,
        ];
    }
}
