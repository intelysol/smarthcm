<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MobilityPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Programs & Policies
            ['code' => 'hcm.mobility.program.view', 'name' => 'View Mobility Programs', 'module' => 'mobility', 'description' => 'View global mobility programs and policies'],
            ['code' => 'hcm.mobility.program.manage', 'name' => 'Manage Mobility Programs', 'module' => 'mobility', 'description' => 'Create and modify global mobility programs and policy versions'],

            // Requests & Eligibility
            ['code' => 'hcm.mobility.request.view', 'name' => 'View Mobility Requests', 'module' => 'mobility', 'description' => 'View mobility candidate applications and requests'],
            ['code' => 'hcm.mobility.request.manage', 'name' => 'Manage Mobility Requests', 'module' => 'mobility', 'description' => 'Create and submit international assignment requests'],
            ['code' => 'hcm.mobility.request.approve', 'name' => 'Approve Mobility Requests', 'module' => 'mobility', 'description' => 'Approve or reject international mobility requests'],

            // Assignments & Versions
            ['code' => 'hcm.mobility.assignment.view', 'name' => 'View Mobility Assignments', 'module' => 'mobility', 'description' => 'View expatriate and international assignment records'],
            ['code' => 'hcm.mobility.assignment.manage', 'name' => 'Manage Mobility Assignments', 'module' => 'mobility', 'description' => 'Create, update, activate and complete mobility assignments'],

            // Costs, Budgets & Allocations
            ['code' => 'hcm.mobility.cost.view', 'name' => 'View Mobility Costs', 'module' => 'mobility', 'description' => 'View cost estimates, budgets, and intercompany allocations'],
            ['code' => 'hcm.mobility.cost.manage', 'name' => 'Manage Mobility Costs', 'module' => 'mobility', 'description' => 'Model assignment costs and configure intercompany cost allocations'],

            // Relocation Cases
            ['code' => 'hcm.mobility.relocation.view', 'name' => 'View Relocation Cases', 'module' => 'mobility', 'description' => 'View relocation status and settling-in checklists'],
            ['code' => 'hcm.mobility.relocation.manage', 'name' => 'Manage Relocation Cases', 'module' => 'mobility', 'description' => 'Coordinate moving providers, housing, and settling-in tasks'],

            // Repatriation & Exit
            ['code' => 'hcm.mobility.repatriation.manage', 'name' => 'Manage Repatriation', 'module' => 'mobility', 'description' => 'Plan and execute assignment completion and repatriation'],

            // Business Travelers & Risk
            ['code' => 'hcm.mobility.traveler.view', 'name' => 'View Business Travelers', 'module' => 'mobility', 'description' => 'Monitor cross-border business travel and compliance risk'],
            ['code' => 'hcm.mobility.traveler.manage', 'name' => 'Manage Business Travelers', 'module' => 'mobility', 'description' => 'Register business travel trips and review PE exposure'],
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
