<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BenefitsAdministrationPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['code' => 'benefits.view.self', 'name' => 'View Own Benefits', 'module' => 'benefits', 'description' => 'View own benefit elections, coverage, and statements in self-service'],
            ['code' => 'benefits.view.team', 'name' => 'View Team Benefits', 'module' => 'benefits', 'description' => 'View non-sensitive team member benefit participation summaries'],
            ['code' => 'benefits.view.hr', 'name' => 'View HR Benefits Administration', 'module' => 'benefits', 'description' => 'View enterprise benefits administration grids and dashboards'],
            ['code' => 'benefits.manage', 'name' => 'Manage Benefit Programs and Plans', 'module' => 'benefits', 'description' => 'Configure benefit programs, plans, coverages, and eligibility rules'],
            ['code' => 'benefits.approve', 'name' => 'Approve Benefit Elections', 'module' => 'benefits', 'description' => 'Approve or reject benefit elections, waivers, and enrollments'],
            ['code' => 'benefits.verify', 'name' => 'Verify Life Event Documents', 'module' => 'benefits', 'description' => 'Verify life event requests and supporting legal documents'],
            ['code' => 'benefits.export', 'name' => 'Export Benefits Data', 'module' => 'benefits', 'description' => 'Export benefit enrollments to insurance and retirement providers'],
            ['code' => 'benefits.admin', 'name' => 'Benefits Full Administrator', 'module' => 'benefits', 'description' => 'Full administrative access to all HCM benefits operations and reconciliation'],
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
