<?php

namespace Database\Seeders;

use App\Domains\Shared\Models\Permission;
use App\Domains\Shared\Models\PermissionGroup;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RecruitmentPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $group = PermissionGroup::firstOrCreate(
            ['name' => 'recruitment'],
            ['label' => 'Recruitment & ATS']
        );

        $permissions = [
            'hcm.recruitment.view' => 'View recruitment management and jobs',
            'hcm.recruitment.manage' => 'Full recruitment and ATS administration',
            'hcm.requisition.view' => 'View job requisitions',
            'hcm.requisition.create' => 'Create new job requisitions',
            'hcm.requisition.edit' => 'Edit job requisitions',
            'hcm.requisition.approve' => 'Approve or reject job requisitions',
            'hcm.requisition.publish' => 'Publish job requisitions to career sites',
            'hcm.candidate.view' => 'View candidate profiles and resumes',
            'hcm.candidate.manage' => 'Manage candidate records and talent pools',
            'hcm.candidate.merge' => 'Merge duplicate candidate profiles',
            'hcm.application.view' => 'View job applications',
            'hcm.application.manage' => 'Manage application stages and status',
            'hcm.application.screen' => 'Screen candidate applications',
            'hcm.interview.view' => 'View interview schedules',
            'hcm.interview.manage' => 'Schedule and manage interviews',
            'hcm.interview.evaluate' => 'Submit interview evaluations and scorecards',
            'hcm.assessment.view' => 'View candidate assessment results',
            'hcm.assessment.manage' => 'Assign and manage candidate assessments',
            'hcm.offer.view' => 'View employment offers',
            'hcm.offer.create' => 'Draft and modify employment offers',
            'hcm.offer.approve' => 'Approve employment offers',
            'hcm.offer.send' => 'Send formal offer to candidate',
            'hcm.hiring.view' => 'View hiring decisions',
            'hcm.hiring.approve' => 'Sanction final hire and Core HR handoff',
            'hcm.recruitment.analytics.view' => 'View recruitment analytics and funnel reports',
            'hcm.recruitment.report.export' => 'Export ATS and recruitment reports',
        ];

        foreach ($permissions as $name => $description) {
            Permission::firstOrCreate(
                ['name' => $name],
                [
                    'permission_group_id' => $group->id,
                    'uuid' => (string) Str::uuid(),
                    'label' => ucwords(str_replace(['hcm.recruitment.', 'hcm.requisition.', 'hcm.candidate.', 'hcm.application.', 'hcm.interview.', 'hcm.offer.', 'hcm.hiring.', '.'], ['', '', '', '', '', '', '', ' '], $name)),
                    'module' => 'recruitment',
                    'resource' => 'recruitment',
                    'action' => 'manage',
                    'description' => $description,
                    'status' => 'active',
                ]
            );
        }
    }
}
