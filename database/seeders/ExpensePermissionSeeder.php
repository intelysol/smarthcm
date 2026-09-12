<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ExpensePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Core Expenses & Policy
            ['code' => 'hcm.expense.view', 'name' => 'View Expenses', 'module' => 'expenses', 'description' => 'View expense dashboard and categories'],
            ['code' => 'hcm.expense.manage', 'name' => 'Manage Expenses', 'module' => 'expenses', 'description' => 'Manage expense categories, rates, and configuration'],
            ['code' => 'hcm.expense.policy.manage', 'name' => 'Manage Expense Policies', 'module' => 'expenses', 'description' => 'Create and configure expense policies and version rules'],

            // Expense Claims
            ['code' => 'hcm.expense.claim.view', 'name' => 'View Expense Claims', 'module' => 'expenses', 'description' => 'View employee expense claims and receipts'],
            ['code' => 'hcm.expense.claim.manage', 'name' => 'Manage Expense Claims', 'module' => 'expenses', 'description' => 'Create and update expense claims'],
            ['code' => 'hcm.expense.claim.submit', 'name' => 'Submit Expense Claims', 'module' => 'expenses', 'description' => 'Submit expense claims for approval'],
            ['code' => 'hcm.expense.claim.approve', 'name' => 'Approve Expense Claims', 'module' => 'expenses', 'description' => 'Manager approval of expense claims'],
            ['code' => 'hcm.expense.claim.reject', 'name' => 'Reject Expense Claims', 'module' => 'expenses', 'description' => 'Reject or return expense claims'],

            // Travel Requests & Authorizations
            ['code' => 'hcm.expense.travel.view', 'name' => 'View Travel Requests', 'module' => 'expenses', 'description' => 'View business travel requests and itineraries'],
            ['code' => 'hcm.expense.travel.manage', 'name' => 'Manage Travel Requests', 'module' => 'expenses', 'description' => 'Submit and modify business travel requests'],
            ['code' => 'hcm.expense.travel.approve', 'name' => 'Approve Travel Requests', 'module' => 'expenses', 'description' => 'Authorize travel requests and generate travel authorizations'],

            // Travel Advances
            ['code' => 'hcm.expense.advance.view', 'name' => 'View Travel Advances', 'module' => 'expenses', 'description' => 'View travel advances and settlement history'],
            ['code' => 'hcm.expense.advance.manage', 'name' => 'Manage Travel Advances', 'module' => 'expenses', 'description' => 'Submit travel advance requests'],
            ['code' => 'hcm.expense.advance.approve', 'name' => 'Approve Travel Advances', 'module' => 'expenses', 'description' => 'Approve travel advance requests'],
            ['code' => 'hcm.expense.advance.disburse', 'name' => 'Disburse Travel Advances', 'module' => 'expenses', 'description' => 'Record advance disbursements from finance'],

            // Reimbursements
            ['code' => 'hcm.expense.reimbursement.view', 'name' => 'View Reimbursements', 'module' => 'expenses', 'description' => 'View reimbursement batches and employee payables'],
            ['code' => 'hcm.expense.reimbursement.manage', 'name' => 'Manage Reimbursements', 'module' => 'expenses', 'description' => 'Create and process reimbursement batches'],
            ['code' => 'hcm.expense.reimbursement.approve', 'name' => 'Approve Reimbursements', 'module' => 'expenses', 'description' => 'Finance authorization for reimbursement settlement'],

            // Corporate Cards
            ['code' => 'hcm.expense.corporate_card.view', 'name' => 'View Corporate Cards', 'module' => 'expenses', 'description' => 'View corporate cards and imported transactions'],
            ['code' => 'hcm.expense.corporate_card.manage', 'name' => 'Manage Corporate Cards', 'module' => 'expenses', 'description' => 'Assign cards and match transactions to expenses'],

            // Accounting Export & Reports
            ['code' => 'hcm.expense.accounting.export', 'name' => 'Export Expense Accounting', 'module' => 'expenses', 'description' => 'Generate and post GL accounting export batches'],
            ['code' => 'hcm.expense.report.view', 'name' => 'View Expense Reports', 'module' => 'expenses', 'description' => 'View expense analytics, trends, and violation reports'],
            ['code' => 'hcm.expense.report.export', 'name' => 'Export Expense Reports', 'module' => 'expenses', 'description' => 'Export expense and travel reports'],
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
