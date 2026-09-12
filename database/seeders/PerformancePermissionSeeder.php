<?php

namespace Database\Seeders;

use App\Domains\Shared\Models\Permission;
use App\Domains\Shared\Models\PermissionGroup;
use Illuminate\Database\Seeder;

class PerformancePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $group = PermissionGroup::query()->firstOrCreate(['name' => 'performance'], ['label' => 'Performance Management']);
        foreach (['hcm.performance.view', 'hcm.performance.create', 'hcm.performance.edit', 'hcm.performance.goal.view', 'hcm.performance.goal.create', 'hcm.performance.goal.edit', 'hcm.performance.goal.approve', 'hcm.performance.review.view', 'hcm.performance.review.create', 'hcm.performance.review.submit', 'hcm.performance.review.reopen', 'hcm.performance.feedback.view', 'hcm.performance.feedback.manage', 'hcm.performance.competency.view', 'hcm.performance.competency.manage', 'hcm.performance.cycle.view', 'hcm.performance.cycle.manage', 'hcm.performance.cycle.publish', 'hcm.performance.calibration.view', 'hcm.performance.calibration.manage', 'hcm.performance.development.view', 'hcm.performance.development.manage', 'hcm.performance.pip.view', 'hcm.performance.pip.manage', 'hcm.performance.recognition.view', 'hcm.performance.recognition.manage', 'hcm.performance.report.view', 'hcm.performance.report.export', 'hcm.performance.confidential.view', 'hcm.performance.restricted.view'] as $name) { Permission::query()->firstOrCreate(['name' => $name], ['permission_group_id' => $group->id, 'label' => str($name)->replace(['hcm.', '.', '_'], ['', ' ', ' '])->title(), 'module' => 'performance']); }
    }
}
