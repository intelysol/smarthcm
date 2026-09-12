<?php

namespace Database\Seeders;

use App\Domains\Shared\Models\Permission;
use App\Domains\Shared\Models\PermissionGroup;
use Illuminate\Database\Seeder;

class BusinessRulesSeeder extends Seeder
{
    public function run(): void
    {
        $group = PermissionGroup::query()->firstOrCreate(['name' => 'rules'], ['label' => 'Business Rules']);
        foreach (['rules.view' => 'View business rules', 'rules.manage' => 'Manage business rules', 'rules.execute' => 'Test and execute business rules', 'rules.audit.view' => 'View rule audit history'] as $name => $label) { Permission::query()->firstOrCreate(['name' => $name], ['permission_group_id' => $group->id, 'label' => $label]); }
    }
}
