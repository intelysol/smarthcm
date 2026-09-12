<?php

namespace Database\Seeders;

use App\Domains\Shared\Models\Permission;
use App\Domains\Shared\Models\PermissionGroup;
use Illuminate\Database\Seeder;

class EmployeePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $group = PermissionGroup::query()->firstOrCreate(
            ['name' => 'employee'],
            ['label' => 'Employee Core'],
        );

        foreach (['view', 'create', 'update', 'delete', 'import', 'export', 'documents', 'timeline', 'salary', 'bank', 'personal', 'transfer', 'promote', 'terminate'] as $action) {
            Permission::query()->firstOrCreate(
                ['name' => "employee.{$action}"],
                ['permission_group_id' => $group->id, 'label' => 'Employee '.str($action)->replace('-', ' ')->title()],
            );
        }

        foreach ([
            'ess.view' => 'Employee self-service dashboard', 'ess.profile' => 'Employee self-service profile', 'ess.documents' => 'Employee self-service documents',
            'manager.dashboard' => 'Manager dashboard', 'manager.team' => 'Manager team', 'manager.approvals' => 'Manager approvals',
            'manager.directory' => 'Manager directory', 'manager.organization' => 'Manager organization chart',
            'notification.view' => 'Notification center', 'announcement.view' => 'Announcement view', 'announcement.manage' => 'Announcement management',
        ] as $name => $label) {
            Permission::query()->firstOrCreate(['name' => $name], ['permission_group_id' => $group->id, 'label' => $label]);
        }
    }
}
