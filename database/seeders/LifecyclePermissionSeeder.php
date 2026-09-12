<?php

namespace Database\Seeders;

use App\Domains\Shared\Models\Permission;
use App\Domains\Shared\Models\PermissionGroup;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LifecyclePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $group = PermissionGroup::firstOrCreate(
            ['name' => 'lifecycle'],
            ['label' => 'Employee Lifecycle & Personnel Actions']
        );

        $permissions = [
            'personnel_actions.view' => 'View personnel action requests and details',
            'personnel_actions.create' => 'Draft new personnel action requests',
            'personnel_actions.update' => 'Modify draft personnel action requests',
            'personnel_actions.submit' => 'Submit personnel action requests for approval',
            'personnel_actions.validate' => 'Run validation and conflict checks on personnel actions',
            'personnel_actions.approve' => 'Approve personnel action requests',
            'personnel_actions.reject' => 'Reject personnel action requests',
            'personnel_actions.execute' => 'Execute approved personnel actions to Core HR',
            'personnel_actions.cancel' => 'Cancel pending personnel action requests',
            'personnel_actions.reverse' => 'Initiate and approve compensating reversal actions',
            'personnel_actions.bulk' => 'Author and execute bulk personnel actions',
            'personnel_actions.backdate' => 'Authorize and submit backdated personnel actions',
            'personnel_actions.view_compensation' => 'View compensation and salary modification details',
            'personnel_actions.view_sensitive' => 'View confidential managerial and HR notes',
            'personnel_actions.manage_types' => 'Configure personnel action types and categories',
            'personnel_actions.view_audit' => 'Inspect immutable personnel action audit history',
            'personnel_actions.self.view' => 'Employee self-service view of personal lifecycle actions',
            'personnel_actions.self.acknowledge' => 'Employee electronic acknowledgement of promotions and transfers',
        ];

        foreach ($permissions as $name => $description) {
            Permission::firstOrCreate(
                ['name' => $name],
                [
                    'permission_group_id' => $group->id,
                    'uuid' => (string) Str::uuid(),
                    'label' => ucwords(str_replace(['personnel_actions.', '.'], ['', ' '], $name)),
                    'module' => 'lifecycle',
                    'resource' => 'personnel_actions',
                    'action' => 'manage',
                    'description' => $description,
                    'status' => 'active',
                ]
            );
        }
    }
}
