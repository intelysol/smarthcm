<?php

namespace Database\Seeders;

use App\Domains\Shared\Models\Permission;
use App\Domains\Shared\Models\PermissionGroup;
use Illuminate\Database\Seeder;

class CompensationPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $group = PermissionGroup::query()->firstOrCreate(
            ['name' => 'compensation'],
            ['label' => 'Compensation Management']
        );

        $permissions = [
            'hcm.compensation.view',
            'hcm.compensation.create',
            'hcm.compensation.edit',
            'hcm.compensation.delete',
            'hcm.compensation.cycle.view',
            'hcm.compensation.cycle.manage',
            'hcm.compensation.cycle.publish',
            'hcm.compensation.budget.view',
            'hcm.compensation.budget.manage',
            'hcm.compensation.matrix.view',
            'hcm.compensation.matrix.manage',
            'hcm.compensation.recommendation.view',
            'hcm.compensation.recommendation.propose',
            'hcm.compensation.recommendation.approve',
            'hcm.compensation.calibration.view',
            'hcm.compensation.calibration.manage',
            'hcm.compensation.export.payroll',
            'hcm.compensation.totalrewards.view',
            'hcm.compensation.totalrewards.generate',
            'hcm.compensation.equity.view',
            'hcm.compensation.confidential.view',
        ];

        foreach ($permissions as $name) {
            Permission::query()->firstOrCreate(
                ['name' => $name],
                [
                    'permission_group_id' => $group->id,
                    'label' => str($name)->replace(['hcm.', '.', '_'], ['', ' ', ' '])->title(),
                    'module' => 'compensation',
                ]
            );
        }
    }
}
