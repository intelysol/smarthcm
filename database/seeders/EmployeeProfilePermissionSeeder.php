<?php

namespace Database\Seeders;

use App\Domains\Shared\Models\Permission;
use App\Domains\Shared\Models\PermissionGroup;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EmployeeProfilePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $group = PermissionGroup::firstOrCreate(
            ['name' => 'employee_profile'],
            ['label' => 'Employee Profile & Directory Management']
        );

        $permissions = [
            'employee_profile.view' => 'View employee profiles',
            'employee_profile.edit_self' => 'Edit self-service profile details',
            'employee_profile.directory' => 'Access employee directory',
            'employee_profile.org_chart' => 'View organization chart',
            'employee_profile.people_search' => 'Search employees globally',
            'employee_profile.change_request' => 'Submit profile change requests',
            'employee_profile.approve_change' => 'Approve employee profile change requests',
            'employee_profile.reject_change' => 'Reject employee profile change requests',
            'employee_profile.view_sensitive' => 'View sensitive compensation and profile fields',
            'employee_profile.view_team' => 'View managerial team structure and reports',
            'employee_profile.manage_preferences' => 'Manage profile privacy preferences',
            'employee_profile.rebuild_projection' => 'Rebuild employee org read models',
            'employee_profile.ai_search' => 'Perform AI-assisted people search',
        ];

        foreach ($permissions as $name => $description) {
            Permission::firstOrCreate(
                ['name' => $name],
                [
                    'permission_group_id' => $group->id,
                    'uuid' => (string) Str::uuid(),
                    'label' => $description,
                ]
            );
        }
    }
}
