<?php

namespace Database\Seeders;

use App\Domains\Shared\Models\Permission;
use App\Domains\Shared\Models\PermissionGroup;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LearningPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $group = PermissionGroup::firstOrCreate(
            ['name' => 'learning'],
            ['label' => 'Learning & Development']
        );

        $permissions = [
            'hcm.learning.view' => 'View learning management and catalog',
            'hcm.learning.manage' => 'Full administrative access to learning management',
            'hcm.learning.catalog.view' => 'View course catalog and offerings',
            'hcm.learning.catalog.manage' => 'Manage course catalog categories and taxonomies',
            'hcm.learning.course.create' => 'Create new courses and training programs',
            'hcm.learning.course.edit' => 'Edit course content, lessons, and modules',
            'hcm.learning.course.publish' => 'Publish or archive courses',
            'hcm.learning.enrollment.view' => 'View employee training enrollments',
            'hcm.learning.enrollment.manage' => 'Manage enrollments and training waitlists',
            'hcm.learning.enrollment.approve' => 'Approve or reject training enrollment requests',
            'hcm.learning.assessment.manage' => 'Author and manage assessments, quizzes, and question bank',
            'hcm.learning.assessment.view' => 'View assessment scores and attempt history',
            'hcm.learning.certificate.view' => 'View and download course completion certificates',
            'hcm.learning.certificate.manage' => 'Issue, verify, or revoke certificates',
            'hcm.learning.certification.view' => 'View professional certifications and licenses',
            'hcm.learning.certification.manage' => 'Manage employee certifications and renewal cycles',
            'hcm.learning.development.view' => 'View Individual Development Plans (IDP)',
            'hcm.learning.development.manage' => 'Create and manage IDPs and development goals',
            'hcm.learning.budget.view' => 'View training budgets and spend',
            'hcm.learning.budget.manage' => 'Allocate and manage departmental training budgets',
            'hcm.learning.analytics.view' => 'Access learning analytics, completion rates, and dashboards',
            'hcm.learning.report.export' => 'Export compliance and training progress reports',
        ];

        foreach ($permissions as $name => $description) {
            Permission::firstOrCreate(
                ['name' => $name],
                [
                    'permission_group_id' => $group->id,
                    'uuid' => (string) Str::uuid(),
                    'label' => ucwords(str_replace(['hcm.learning.', '.'], ['', ' '], $name)),
                    'module' => 'learning',
                    'resource' => 'learning',
                    'action' => 'manage',
                    'description' => $description,
                    'status' => 'active',
                ]
            );
        }
    }
}
