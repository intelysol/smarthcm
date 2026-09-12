<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class WorkforcePlanningPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'hcm.workforce_planning.view',
            'hcm.workforce_planning.manage',
            'hcm.workforce_plan.create',
            'hcm.workforce_plan.edit',
            'hcm.workforce_plan.submit',
            'hcm.workforce_plan.approve',
            'hcm.workforce_plan.lock',
            'hcm.position_plan.view',
            'hcm.position_plan.manage',
            'hcm.hiring_plan.view',
            'hcm.hiring_plan.manage',
            'hcm.workforce_scenario.view',
            'hcm.workforce_scenario.manage',
            'hcm.workforce_cost.view',
            'hcm.workforce_cost.manage',
            'hcm.workforce_sensitive.view',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'api']);
        }
    }
}
