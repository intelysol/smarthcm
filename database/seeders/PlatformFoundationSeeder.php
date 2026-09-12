<?php

namespace Database\Seeders;

use App\Domains\Shared\Models\Permission;
use App\Domains\Shared\Models\PermissionGroup;
use Illuminate\Database\Seeder;

class PlatformFoundationSeeder extends Seeder
{
    public function run(): void
    {
        $group = PermissionGroup::query()->firstOrCreate(['name' => 'platform'], ['label' => 'Platform Foundation']);
        foreach ([
            'platform.roles.view' => 'View roles',
            'platform.roles.manage' => 'Manage roles',
            'platform.users.view' => 'View users',
            'platform.users.manage' => 'Manage users',
            'platform.audit.view' => 'View audit records',
            'platform.settings.manage' => 'Manage platform settings',
            'platform.tenants.manage' => 'Manage tenants',
        ] as $name => $label) {
            Permission::query()->firstOrCreate(['name' => $name], ['permission_group_id' => $group->id, 'label' => $label]);
        }
    }
}
