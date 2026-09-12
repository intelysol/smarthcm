<?php

namespace Database\Seeders;

use App\Domains\Shared\Models\Permission;
use App\Domains\Shared\Models\PermissionGroup;
use Illuminate\Database\Seeder;

class CareerPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $careerGroup = PermissionGroup::query()->firstOrCreate(
            ['name' => 'career'],
            ['label' => 'Career Management']
        );

        $talentGroup = PermissionGroup::query()->firstOrCreate(
            ['name' => 'talent'],
            ['label' => 'Talent Management']
        );

        $successionGroup = PermissionGroup::query()->firstOrCreate(
            ['name' => 'succession'],
            ['label' => 'Succession Planning']
        );

        $permissions = [
            // Career Management
            ['name' => 'hcm.career.view', 'label' => 'View Career Information', 'group_id' => $careerGroup->id],
            ['name' => 'hcm.career.manage', 'label' => 'Manage Career Settings', 'group_id' => $careerGroup->id],
            ['name' => 'hcm.career.skills.view', 'label' => 'View Skill Catalog & Profiles', 'group_id' => $careerGroup->id],
            ['name' => 'hcm.career.skills.manage', 'label' => 'Manage Skill Catalog', 'group_id' => $careerGroup->id],
            ['name' => 'hcm.career.skills.verify', 'label' => 'Verify Employee Skills', 'group_id' => $careerGroup->id],
            ['name' => 'hcm.career.plan.view', 'label' => 'View Career Plans', 'group_id' => $careerGroup->id],
            ['name' => 'hcm.career.plan.create', 'label' => 'Create Career Plans', 'group_id' => $careerGroup->id],
            ['name' => 'hcm.career.plan.manage', 'label' => 'Manage Career Plans', 'group_id' => $careerGroup->id],
            ['name' => 'hcm.career.plan.approve', 'label' => 'Approve Career Plans', 'group_id' => $careerGroup->id],
            ['name' => 'hcm.career.mobility.view', 'label' => 'View Internal Mobility', 'group_id' => $careerGroup->id],
            ['name' => 'hcm.career.mobility.manage', 'label' => 'Manage Internal Mobility', 'group_id' => $careerGroup->id],
            ['name' => 'hcm.career.mentoring.view', 'label' => 'View Mentoring Programs', 'group_id' => $careerGroup->id],
            ['name' => 'hcm.career.mentoring.manage', 'label' => 'Manage Mentoring Programs', 'group_id' => $careerGroup->id],

            // Talent Management
            ['name' => 'hcm.talent.view', 'label' => 'View Talent Hub', 'group_id' => $talentGroup->id],
            ['name' => 'hcm.talent.manage', 'label' => 'Manage Talent Settings', 'group_id' => $talentGroup->id],
            ['name' => 'hcm.talent.pool.view', 'label' => 'View Talent Pools', 'group_id' => $talentGroup->id],
            ['name' => 'hcm.talent.pool.manage', 'label' => 'Manage Talent Pools', 'group_id' => $talentGroup->id],
            ['name' => 'hcm.talent.review.view', 'label' => 'View Talent Reviews', 'group_id' => $talentGroup->id],
            ['name' => 'hcm.talent.review.manage', 'label' => 'Manage Talent Reviews', 'group_id' => $talentGroup->id],
            ['name' => 'hcm.talent.matrix.view', 'label' => 'View 9-Box Matrix', 'group_id' => $talentGroup->id],
            ['name' => 'hcm.talent.matrix.manage', 'label' => 'Manage 9-Box Calibrations', 'group_id' => $talentGroup->id],
            ['name' => 'hcm.talent.confidential.view', 'label' => 'View Confidential Talent Data', 'group_id' => $talentGroup->id],
            ['name' => 'hcm.talent.restricted.view', 'label' => 'View Restricted Talent Data', 'group_id' => $talentGroup->id],
            ['name' => 'hcm.talent.report.view', 'label' => 'View Talent Reports', 'group_id' => $talentGroup->id],
            ['name' => 'hcm.talent.report.export', 'label' => 'Export Talent Reports', 'group_id' => $talentGroup->id],

            // Succession Planning
            ['name' => 'hcm.succession.view', 'label' => 'View Succession Plans', 'group_id' => $successionGroup->id],
            ['name' => 'hcm.succession.manage', 'label' => 'Manage Succession Plans', 'group_id' => $successionGroup->id],
            ['name' => 'hcm.succession.calibration', 'label' => 'Calibrate Succession Candidates', 'group_id' => $successionGroup->id],
        ];

        foreach ($permissions as $p) {
            Permission::query()->updateOrCreate(
                ['name' => $p['name']],
                ['permission_group_id' => $p['group_id'], 'label' => $p['label']]
            );
        }
    }
}
