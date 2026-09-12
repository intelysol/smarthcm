<?php

namespace App\Domains\WorkforceAdmin\Services;

use App\Domains\Employee\Models\Employee;

class CrossDomainImpactAnalysisService
{
    /**
     * Perform cross-domain impact analysis for proposed workforce administrative operations.
     * Returns structured impacted domains, risk level, required actions, and status.
     */
    public function analyzeImpact(string $operationType, array $proposedChanges, ?string $employeeId = null): array
    {
        $employee = $employeeId ? Employee::with(['department', 'designation', 'branch'])->find($employeeId) : null;
        $impacts = [];
        $overallRisk = 'low';

        // 1. Department / Cost Center change
        if (isset($proposedChanges['department_id']) || $operationType === 'bulk_department_update') {
            $impacts[] = [
                'domain' => 'payroll',
                'affected_area' => 'Cost Center & General Ledger Allocation',
                'required_action' => 'Verify payroll cost center code and budget allocation for target department.',
                'risk' => 'medium',
                'status' => 'action_required',
            ];
            $impacts[] = [
                'domain' => 'benefits',
                'affected_area' => 'Departmental Benefit Eligibility',
                'required_action' => 'Review department-specific health plans or perk eligibility.',
                'risk' => 'low',
                'status' => 'informational',
            ];
            $impacts[] = [
                'domain' => 'expenses',
                'affected_area' => 'Expense Approval Hierarchy & Policy',
                'required_action' => 'Update expense approver routing and cost center assignment.',
                'risk' => 'medium',
                'status' => 'action_required',
            ];
            $impacts[] = [
                'domain' => 'learning',
                'affected_area' => 'Mandatory Department Training',
                'required_action' => 'Enroll in department-specific compliance and role learning tracks.',
                'risk' => 'low',
                'status' => 'pending_review',
            ];
            $overallRisk = 'medium';
        }

        // 2. Manager / Reporting line change
        if (isset($proposedChanges['reporting_manager_id']) || $operationType === 'bulk_manager_update') {
            $impacts[] = [
                'domain' => 'workflow',
                'affected_area' => 'Approval Routing & Delegation Hierarchy',
                'required_action' => 'Re-route pending leave, expense, and appraisal approvals to new manager.',
                'risk' => 'high',
                'status' => 'action_required',
            ];
            $impacts[] = [
                'domain' => 'performance',
                'affected_area' => 'Performance Goal & Review Assessment',
                'required_action' => 'Transfer in-progress performance appraisal ownership to new manager.',
                'risk' => 'medium',
                'status' => 'action_required',
            ];
            $overallRisk = 'high';
        }

        // 3. Designation / Grade / Job Change
        if (isset($proposedChanges['designation_id']) || isset($proposedChanges['job_id']) || $operationType === 'bulk_job_change') {
            $impacts[] = [
                'domain' => 'compensation',
                'affected_area' => 'Salary Band & Grade Alignment',
                'required_action' => 'Audit base pay against newly designated pay grade range.',
                'risk' => 'high',
                'status' => 'action_required',
            ];
            $impacts[] = [
                'domain' => 'compliance',
                'affected_area' => 'Role-specific Work Permits & Licenses',
                'required_action' => 'Verify professional license or regulatory certifications for target job.',
                'risk' => 'high',
                'status' => 'action_required',
            ];
            $overallRisk = 'high';
        }

        // 4. Employment Status Change (e.g. Leave, Suspension, Termination)
        if (isset($proposedChanges['employment_status']) || $operationType === 'bulk_status_change') {
            $impacts[] = [
                'domain' => 'payroll',
                'affected_area' => 'Salary Disbursement & Cutoff',
                'required_action' => 'Place payroll hold or compute final settlement.',
                'risk' => 'critical',
                'status' => 'action_required',
            ];
            $impacts[] = [
                'domain' => 'benefits',
                'affected_area' => 'Benefit Coverage Continuation / COBRA',
                'required_action' => 'Trigger life event notice and determine coverage termination date.',
                'risk' => 'high',
                'status' => 'action_required',
            ];
            $overallRisk = 'critical';
        }

        return [
            'operation_type' => $operationType,
            'employee' => $employee ? [
                'id' => $employee->id,
                'name' => "{$employee->first_name} {$employee->last_name}",
                'employee_number' => $employee->employee_number,
                'current_department' => $employee->department?->name,
            ] : null,
            'overall_risk' => $overallRisk,
            'impacted_domains_count' => count($impacts),
            'impacts' => $impacts,
        ];
    }
}
