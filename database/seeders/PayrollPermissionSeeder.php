<?php

namespace Database\Seeders;

use App\Domains\Shared\Models\Permission;
use App\Domains\Shared\Models\PermissionGroup;
use Illuminate\Database\Seeder;

class PayrollPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $group = PermissionGroup::query()->firstOrCreate(
            ['name' => 'payroll'],
            ['label' => 'Payroll & Compensation Management']
        );

        $permissions = [
            'hcm.payroll.view' => 'View payroll overview and status',
            'hcm.payroll.manage' => 'Full administrative access to payroll module',
            'hcm.payroll.period.view' => 'View payroll periods and schedules',
            'hcm.payroll.period.manage' => 'Create and modify payroll periods',
            'hcm.payroll.period.lock' => 'Lock payroll periods at cutoff',
            'hcm.payroll.period.reopen' => 'Reopen locked payroll periods with audit trail',
            'hcm.payroll.calculate' => 'Execute payroll calculation runs',
            'hcm.payroll.review' => 'Review payroll calculations, variances and exceptions',
            'hcm.payroll.approve' => 'Approve payroll runs for disbursement',
            'hcm.payroll.salary.view' => 'View confidential employee salary details',
            'hcm.payroll.salary.manage' => 'Define components and structures',
            'hcm.payroll.salary.approve' => 'Approve employee compensation revisions',
            'hcm.payroll.adjust' => 'Request manual payroll adjustments and arrears',
            'hcm.payroll.adjust.approve' => 'Approve manual payroll adjustments',
            'hcm.payroll.payslip.view' => 'View generated employee payslips',
            'hcm.payroll.payslip.download' => 'Download and export employee payslips',
            'hcm.payroll.payment.view' => 'View bank payment batches',
            'hcm.payroll.payment.manage' => 'Generate and submit payment batches',
            'hcm.payroll.payment.export' => 'Export bank disbursement files',
            'hcm.payroll.accounting.export' => 'Generate and export double-entry accounting journal vouchers',
            'hcm.payroll.report.view' => 'View payroll reports and analytics',
            'hcm.payroll.report.export' => 'Export payroll financial and variance reports',
            'hcm.payroll.tax.manage' => 'Manage tax rules and versioned brackets',
            'hcm.payroll.policy.manage' => 'Configure payroll policies and proration rules',
        ];

        foreach ($permissions as $name => $label) {
            Permission::query()->firstOrCreate(
                ['name' => $name],
                [
                    'permission_group_id' => $group->id,
                    'label' => $label,
                ]
            );
        }
    }
}
