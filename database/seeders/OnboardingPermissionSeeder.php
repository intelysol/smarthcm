<?php

namespace Database\Seeders;

use App\Domains\Shared\Models\Permission;
use App\Domains\Shared\Models\PermissionGroup;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OnboardingPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $group = PermissionGroup::firstOrCreate(
            ['name' => 'onboarding'],
            ['label' => 'Onboarding & Preboarding']
        );

        $permissions = [
            'hcm.onboarding.view' => 'View onboarding cases and checklists',
            'hcm.onboarding.manage' => 'Full onboarding lifecycle administration',
            'hcm.onboarding.template.view' => 'View onboarding templates and tasks',
            'hcm.onboarding.template.manage' => 'Create and modify onboarding templates',
            'hcm.onboarding.case.view' => 'View specific onboarding cases',
            'hcm.onboarding.case.manage' => 'Manage onboarding cases and milestones',
            'hcm.onboarding.task.view' => 'View onboarding tasks',
            'hcm.onboarding.task.manage' => 'Assign and modify onboarding tasks',
            'hcm.onboarding.document.view' => 'View submitted onboarding documents',
            'hcm.onboarding.document.manage' => 'Manage document collection requirements',
            'hcm.onboarding.document.verify' => 'Verify or reject candidate onboarding documents',
            'hcm.onboarding.form.view' => 'View digital joiner forms',
            'hcm.onboarding.form.manage' => 'Manage joiner forms and schemas',
            'hcm.onboarding.approve' => 'Approve onboarding exceptions and milestones',
            'hcm.onboarding.probation.view' => 'View employee probation status',
            'hcm.onboarding.probation.manage' => 'Conduct probation reviews and extensions',
            'hcm.onboarding.analytics.view' => 'View onboarding analytics and readiness KPIs',
            'hcm.onboarding.report.export' => 'Export onboarding reports and checklists',
            'hcm.self.onboarding.view' => 'New hire view of personal onboarding portal',
            'hcm.self.onboarding.manage' => 'New hire completion of onboarding tasks and forms',
        ];

        foreach ($permissions as $name => $description) {
            Permission::firstOrCreate(
                ['name' => $name],
                [
                    'permission_group_id' => $group->id,
                    'uuid' => (string) Str::uuid(),
                    'label' => ucwords(str_replace(['hcm.onboarding.', 'hcm.self.onboarding.', '.'], ['', 'Self ', ' '], $name)),
                    'module' => 'onboarding',
                    'resource' => 'onboarding',
                    'action' => 'manage',
                    'description' => $description,
                    'status' => 'active',
                ]
            );
        }
    }
}
