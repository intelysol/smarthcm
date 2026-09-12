<?php

namespace Database\Seeders;

use App\Domains\Shared\Models\Permission;
use App\Domains\Shared\Models\PermissionGroup;
use Illuminate\Database\Seeder;

class MetadataPlatformSeeder extends Seeder
{
    public function run(): void
    {
        $group = PermissionGroup::query()->firstOrCreate(['name' => 'metadata'], ['label' => 'Metadata Platform']);
        foreach (['metadata.entities.view' => 'View metadata entities', 'metadata.entities.manage' => 'Manage metadata entities', 'metadata.records.manage' => 'Manage metadata records', 'metadata.artifacts.manage' => 'Manage metadata artifacts', 'metadata.audit.view' => 'View metadata audit history'] as $name => $label) {
            Permission::query()->firstOrCreate(['name' => $name], ['permission_group_id' => $group->id, 'label' => $label]);
        }
    }
}
