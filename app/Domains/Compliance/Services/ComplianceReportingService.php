<?php

namespace App\Domains\Compliance\Services;

use App\Domains\Compliance\Models\HcmComplianceExemption;
use App\Domains\Compliance\Models\HcmEmployeeComplianceRequirement;
use App\Domains\Compliance\Models\HcmEmployeeComplianceSnapshot;
use App\Domains\Compliance\Models\HcmEmployeeLicense;
use App\Domains\Compliance\Models\HcmEmployeeVisaRecord;
use App\Domains\Compliance\Models\HcmEmployeeWorkPermit;
use App\Domains\Employee\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ComplianceReportingService
{
    /**
     * Dashboard KPI summary across tenant.
     */
    public function getComplianceDashboardSummary(string $tenantId): array
    {
        $totalEmployees = Employee::where('tenant_id', $tenantId)->count();
        $snapshots = HcmEmployeeComplianceSnapshot::where('tenant_id', $tenantId)->get();

        $compliant = $snapshots->where('overall_status', 'compliant')->count();
        $atRisk = $snapshots->where('overall_status', 'at_risk')->count();
        $nonCompliant = $snapshots->where('overall_status', 'non_compliant')->count();
        $exempt = $snapshots->where('overall_status', 'exempt')->count();

        $avgScore = $snapshots->isNotEmpty() ? round($snapshots->avg('compliance_score'), 1) : 100.0;
        $totalExpiring = (int) $snapshots->sum('expiring_count');
        $totalExpired = (int) $snapshots->sum('expired_count');

        return [
            'total_employees' => $totalEmployees,
            'evaluated_employees' => $snapshots->count(),
            'compliant_count' => $compliant,
            'at_risk_count' => $atRisk,
            'non_compliant_count' => $nonCompliant,
            'exempt_count' => $exempt,
            'average_compliance_score' => $avgScore,
            'total_expiring_items' => $totalExpiring,
            'total_expired_items' => $totalExpired,
        ];
    }

    /**
     * Report of all active work permits.
     */
    public function getWorkPermitReport(string $tenantId): Collection
    {
        return HcmEmployeeWorkPermit::with('employee')
            ->where('tenant_id', $tenantId)
            ->where('is_current', true)
            ->orderBy('expiry_date')
            ->get();
    }

    /**
     * Report of visas and expirations.
     */
    public function getVisaExpiryReport(string $tenantId): Collection
    {
        return HcmEmployeeVisaRecord::with('employee')
            ->where('tenant_id', $tenantId)
            ->where('is_current', true)
            ->orderBy('expiry_date')
            ->get();
    }

    /**
     * Report of professional licenses.
     */
    public function getLicenseExpiryReport(string $tenantId): Collection
    {
        return HcmEmployeeLicense::with('employee')
            ->where('tenant_id', $tenantId)
            ->where('is_current', true)
            ->orderBy('expiry_date')
            ->get();
    }

    /**
     * Report of all non-compliant requirement assignments.
     */
    public function getNonComplianceReport(string $tenantId): Collection
    {
        return HcmEmployeeComplianceRequirement::with(['employee', 'requirement'])
            ->where('tenant_id', $tenantId)
            ->whereIn('status', ['non_compliant', 'expired', 'required'])
            ->orderBy('due_date')
            ->get();
    }

    /**
     * Report of all approved and pending exemptions.
     */
    public function getExemptionReport(string $tenantId): Collection
    {
        return HcmComplianceExemption::with(['employee', 'requirement', 'approver'])
            ->where('tenant_id', $tenantId)
            ->orderByDesc('created_at')
            ->get();
    }
}
