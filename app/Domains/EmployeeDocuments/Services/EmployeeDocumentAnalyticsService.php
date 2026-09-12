<?php

namespace App\Domains\EmployeeDocuments\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\EmployeeDocuments\Enums\DocumentStatus;
use App\Domains\EmployeeDocuments\Enums\VerificationStatus;
use App\Domains\EmployeeDocuments\Models\EmployeeDocument;
use App\Domains\EmployeeDocuments\Models\EmployeeDocumentRequirement;
use Carbon\Carbon;

class EmployeeDocumentAnalyticsService
{
    public function getMetrics(string $tenantId): array
    {
        $totalDocs = EmployeeDocument::where('tenant_id', $tenantId)->count();
        $verifiedDocs = EmployeeDocument::where('tenant_id', $tenantId)
            ->where('verification_status', VerificationStatus::VERIFIED->value)
            ->count();
        $pendingVerification = EmployeeDocument::where('tenant_id', $tenantId)
            ->where('verification_status', VerificationStatus::PENDING->value)
            ->count();

        $today = Carbon::today();
        $expiringWithin30 = EmployeeDocument::where('tenant_id', $tenantId)
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '>=', $today->toDateString())
            ->whereDate('expiry_date', '<=', $today->copy()->addDays(30)->toDateString())
            ->count();

        $expiredCount = EmployeeDocument::where('tenant_id', $tenantId)
            ->where(function ($q) use ($today) {
                $q->where('status', DocumentStatus::EXPIRED->value)
                  ->orWhere(function ($sub) use ($today) {
                      $sub->whereNotNull('expiry_date')
                          ->whereDate('expiry_date', '<', $today->toDateString());
                  });
            })
            ->count();

        $totalRequirements = EmployeeDocumentRequirement::where('tenant_id', $tenantId)->count();
        $metRequirements = EmployeeDocumentRequirement::where('tenant_id', $tenantId)
            ->whereIn('status', ['verified', 'waived'])
            ->count();

        $averageCompleteness = $totalRequirements > 0
            ? round(($metRequirements / $totalRequirements) * 100, 1)
            : 100.0;

        return [
            'total_documents' => $totalDocs,
            'verified_documents' => $verifiedDocs,
            'pending_verification' => $pendingVerification,
            'expiring_within_30_days' => $expiringWithin30,
            'expired_documents' => $expiredCount,
            'total_requirements' => $totalRequirements,
            'met_requirements' => $metRequirements,
            'average_completeness_rate' => $averageCompleteness,
        ];
    }
}
