<?php

namespace Database\Seeders;

use App\Domains\Organization\Support\OrganizationEntityRegistry;
use App\Domains\Shared\Models\Permission;
use App\Domains\Shared\Models\PermissionGroup;
use Illuminate\Database\Seeder;

class OrganizationPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $group = PermissionGroup::query()->firstOrCreate(
            ['name' => 'organization'],
            ['label' => 'Organization Management'],
        );

        foreach (app(OrganizationEntityRegistry::class)->all() as $definition) {
            foreach (['view', 'create', 'update', 'delete', 'import', 'export'] as $action) {
                Permission::query()->firstOrCreate(
                    ['name' => "{$definition->permissionPrefix}.{$action}"],
                    [
                        'permission_group_id' => $group->id,
                        'label' => str($definition->permissionPrefix)->replace('-', ' ')->title()." {$action}",
                    ],
                );
            }
        }

        foreach (['organization-chart.view', 'organization-dashboard.view', 'organization-report.view'] as $permission) {
            Permission::query()->firstOrCreate(
                ['name' => $permission],
                [
                    'permission_group_id' => $group->id,
                    'label' => str($permission)->replace(['-', '.'], ' ')->title(),
                ],
            );
        }
    }
}
