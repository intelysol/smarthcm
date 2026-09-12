<?php

namespace App\Domains\Compliance\Services;

use App\Domains\Compliance\Models\HcmEmployeeComplianceRequirement;
use App\Domains\Compliance\Models\HcmEmployeeComplianceSnapshot;
use App\Domains\Employee\Models\Employee;

class ComplianceAiAdvisoryService
{
    protected string $disclaimer = 'This AI analysis is strictly advisory for operational assistance and does not constitute legal, regulatory, or immigration counsel.';

    /**
     * Provide advisory explanation of an employee's compliance posture.
     */
    public function explainComplianceStatus(string $employeeId): array
    {
        $employee = Employee::findOrFail($employeeId);
        $snapshot = HcmEmployeeComplianceSnapshot::where('employee_id', $employeeId)->first();
        $requirements = HcmEmployeeComplianceRequirement::with(['requirement.type'])
            ->where('employee_id', $employeeId)
            ->get();

        $missing = $requirements->whereIn('status', ['non_compliant', 'required'])->pluck('requirement.name')->toArray();
        $expiring = $requirements->where('status', 'expiring')->pluck('requirement.name')->toArray();
        $exempt = $requirements->where('status', 'exempt')->pluck('requirement.name')->toArray();

        $summary = "Employee {$employee->fullName()} is currently evaluated with an overall compliance status of " .
            strtoupper($snapshot?->overall_status ?? 'UNKNOWN') . " and an operational compliance score of " .
            ($snapshot?->compliance_score ?? 100) . "%.";

        $recommendations = [];
        if (!empty($missing)) {
            $recommendations[] = "Immediate action needed: provide valid records or upload verification documents for: " . implode(', ', $missing) . ".";
        }
        if (!empty($expiring)) {
            $recommendations[] = "Upcoming renewals: initiate renewal workflow for " . implode(', ', $expiring) . " within the next 30-60 days to prevent status lapse.";
        }
        if (empty($recommendations)) {
            $recommendations[] = "All mandatory regulatory and organizational compliance items are in good standing.";
        }

        return [
            'employee_id' => $employeeId,
            'overall_status' => $snapshot?->overall_status ?? 'compliant',
            'score' => $snapshot?->compliance_score ?? 100,
            'summary' => $summary,
            'recommendations' => $recommendations,
            'exemptions_noted' => count($exempt),
            'disclaimer' => $this->disclaimer,
            'is_advisory' => true,
        ];
    }

    /**
     * Summarize outstanding compliance items across tenant for executive briefing.
     */
    public function summarizeDashboard(array $dashboardSummary): array
    {
        $atRisk = $dashboardSummary['at_risk_count'] ?? 0;
        $nonCompliant = $dashboardSummary['non_compliant_count'] ?? 0;
        $expiring = $dashboardSummary['total_expiring_items'] ?? 0;

        $urgency = $nonCompliant > 0 ? 'HIGH' : ($atRisk > 0 ? 'MEDIUM' : 'LOW');

        $insights = [
            "Workforce compliance operational readiness is rated at urgency level {$urgency}.",
            "{$nonCompliant} employee(s) currently carry non-compliant or expired regulatory obligations requiring HR intervention.",
            "{$expiring} regulatory document(s) (work permits, visas, or professional licenses) will expire in the next 60 days.",
        ];

        return [
            'urgency' => $urgency,
            'insights' => $insights,
            'disclaimer' => $this->disclaimer,
            'is_advisory' => true,
        ];
    }
}
