<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WorkforceAdminPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Operations View & Manage
            ['code' => 'hcm.operations.view', 'name' => 'View HR Operations', 'module' => 'workforce_admin', 'description' => 'View HR operational queues, cockpit and summaries'],
            ['code' => 'hcm.operations.manage', 'name' => 'Manage HR Operations', 'module' => 'workforce_admin', 'description' => 'Assign and complete operational queue items'],

            // Exceptions
            ['code' => 'hcm.operations.exceptions.view', 'name' => 'View Operational Exceptions', 'module' => 'workforce_admin', 'description' => 'View detected exceptions and error logs'],
            ['code' => 'hcm.operations.exceptions.manage', 'name' => 'Manage Operational Exceptions', 'module' => 'workforce_admin', 'description' => 'Investigate, assign and resolve operational exceptions'],

            // Bulk Operations
            ['code' => 'hcm.operations.bulk.view', 'name' => 'View Bulk Operations', 'module' => 'workforce_admin', 'description' => 'View bulk HR update requests and validation reports'],
            ['code' => 'hcm.operations.bulk.create', 'name' => 'Create Bulk Operations', 'module' => 'workforce_admin', 'description' => 'Draft and validate bulk employee updates'],
            ['code' => 'hcm.operations.bulk.execute', 'name' => 'Execute Bulk Operations', 'module' => 'workforce_admin', 'description' => 'Approve and trigger mass bulk operations'],

            // Governance & Health
            ['code' => 'hcm.operations.governance.manage', 'name' => 'Manage HR Governance', 'module' => 'workforce_admin', 'description' => 'Manage operating rules, calendars, and policies'],
            ['code' => 'hcm.operations.data_quality.view', 'name' => 'View Data Quality', 'module' => 'workforce_admin', 'description' => 'View workforce data quality scores and run scans'],
            ['code' => 'hcm.operations.reconciliation.view', 'name' => 'View Reconciliation', 'module' => 'workforce_admin', 'description' => 'View and trigger cross-domain reconciliation'],
            ['code' => 'hcm.operations.configuration.view', 'name' => 'View Configuration Health', 'module' => 'workforce_admin', 'description' => 'Run diagnostics on HCM platform configuration'],
        ];

        foreach ($permissions as $perm) {
            $exists = DB::table('permissions')->where('code', $perm['code'])->first();
            if (!$exists) {
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
