<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BenefitsPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Core Benefits
            ['code' => 'hcm.benefits.view', 'name' => 'View Benefits', 'module' => 'benefits', 'description' => 'View benefit categories and catalog'],
            ['code' => 'hcm.benefits.manage', 'name' => 'Manage Benefits', 'module' => 'benefits', 'description' => 'Create and modify benefit plans and categories'],
            ['code' => 'hcm.benefits.policy.manage', 'name' => 'Manage Benefit Policies', 'module' => 'benefits', 'description' => 'Manage eligibility and contribution policies'],

            // Enrollments
            ['code' => 'hcm.benefits.enrollment.view', 'name' => 'View Benefit Enrollments', 'module' => 'benefits', 'description' => 'View employee benefit enrollments'],
            ['code' => 'hcm.benefits.enrollment.manage', 'name' => 'Manage Benefit Enrollments', 'module' => 'benefits', 'description' => 'Create and update employee benefit enrollments'],
            ['code' => 'hcm.benefits.enrollment.approve', 'name' => 'Approve Benefit Enrollments', 'module' => 'benefits', 'description' => 'Approve or reject benefit enrollment applications'],

            // Claims & Medical Privacy
            ['code' => 'hcm.benefits.claim.view', 'name' => 'View Benefit Claims', 'module' => 'benefits', 'description' => 'View employee benefit claim summaries'],
            ['code' => 'hcm.benefits.claim.sensitive.view', 'name' => 'View Sensitive Medical Claims', 'module' => 'benefits', 'description' => 'View confidential medical diagnosis details and attachments'],
            ['code' => 'hcm.benefits.claim.manage', 'name' => 'Manage Benefit Claims', 'module' => 'benefits', 'description' => 'Submit and process insurance and reimbursement claims'],
            ['code' => 'hcm.benefits.claim.approve', 'name' => 'Approve Benefit Claims', 'module' => 'benefits', 'description' => 'Approve or reject benefit and insurance claims'],

            // Insurance
            ['code' => 'hcm.benefits.insurance.view', 'name' => 'View Insurance Policies', 'module' => 'benefits', 'description' => 'View insurance policies and coverage certificates'],
            ['code' => 'hcm.benefits.insurance.manage', 'name' => 'Manage Insurance Policies', 'module' => 'benefits', 'description' => 'Manage group insurance policies and providers'],

            // Retirement / Pension
            ['code' => 'hcm.benefits.retirement.view', 'name' => 'View Retirement Plans', 'module' => 'benefits', 'description' => 'View retirement accounts and statements'],
            ['code' => 'hcm.benefits.retirement.manage', 'name' => 'Manage Retirement Plans', 'module' => 'benefits', 'description' => 'Manage retirement plans and contribution policies'],
            ['code' => 'hcm.benefits.retirement.approve', 'name' => 'Approve Retirement Withdrawals', 'module' => 'benefits', 'description' => 'Approve or reject retirement withdrawal requests'],

            // Employee Loans & Advances
            ['code' => 'hcm.benefits.loan.view', 'name' => 'View Employee Loans', 'module' => 'benefits', 'description' => 'View loan applications, schedules, and balances'],
            ['code' => 'hcm.benefits.loan.manage', 'name' => 'Manage Loan Products', 'module' => 'benefits', 'description' => 'Configure loan products and eligibility rules'],
            ['code' => 'hcm.benefits.loan.approve', 'name' => 'Approve Employee Loans', 'module' => 'benefits', 'description' => 'Approve or reject loan applications'],
            ['code' => 'hcm.benefits.loan.disburse', 'name' => 'Disburse Employee Loans', 'module' => 'benefits', 'description' => 'Record loan disbursement and generate schedules'],
            ['code' => 'hcm.benefits.loan.restructure', 'name' => 'Restructure Employee Loans', 'module' => 'benefits', 'description' => 'Authorize loan restructuring and schedule versions'],
            ['code' => 'hcm.benefits.loan.settle', 'name' => 'Settle Employee Loans', 'module' => 'benefits', 'description' => 'Perform early loan settlement and payoff'],

            // Salary Advances
            ['code' => 'hcm.benefits.advance.view', 'name' => 'View Salary Advances', 'module' => 'benefits', 'description' => 'View salary advance requests and recovery schedules'],
            ['code' => 'hcm.benefits.advance.manage', 'name' => 'Manage Salary Advances', 'module' => 'benefits', 'description' => 'Submit and approve salary advances'],

            // Reports & Analytics
            ['code' => 'hcm.benefits.report.view', 'name' => 'View Benefit Reports', 'module' => 'benefits', 'description' => 'View benefit cost, enrollment, and loan portfolio reports'],
            ['code' => 'hcm.benefits.report.export', 'name' => 'Export Benefit Reports', 'module' => 'benefits', 'description' => 'Export benefit and financial reports'],
        ];

        foreach ($permissions as $perm) {
            $exists = DB::table('permissions')->where('code', $perm['code'])->first();
            if (! $exists) {
                DB::table('permissions')->insert([
                    'id' => (string) Str::uuid(),
                    'code' => $perm['code'],
                    'name' => $perm['name'],
                    'module' => $perm['module'],
                    'description' => $perm['description'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
