<?php

namespace Database\Seeders;

use App\Domains\Shared\Models\Permission;
use App\Domains\Shared\Models\PermissionGroup;
use Illuminate\Database\Seeder;

class EngagementPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $engagementGroup = PermissionGroup::query()->firstOrCreate(
            ['name' => 'engagement'],
            ['label' => 'Employee Engagement']
        );

        $surveyGroup = PermissionGroup::query()->firstOrCreate(
            ['name' => 'surveys'],
            ['label' => 'Surveys & Pulse']
        );

        $recognitionGroup = PermissionGroup::query()->firstOrCreate(
            ['name' => 'recognition'],
            ['label' => 'Recognition & Culture']
        );

        $permissions = [
            // General Engagement
            ['name' => 'hcm.engagement.view', 'label' => 'View Engagement Information', 'group_id' => $engagementGroup->id],
            ['name' => 'hcm.engagement.manage', 'label' => 'Manage Engagement Platform', 'group_id' => $engagementGroup->id],
            ['name' => 'hcm.engagement.analytics.view', 'label' => 'View Engagement Analytics', 'group_id' => $engagementGroup->id],
            ['name' => 'hcm.engagement.analytics.export', 'label' => 'Export Engagement Analytics', 'group_id' => $engagementGroup->id],

            // Surveys & Campaigns
            ['name' => 'hcm.engagement.survey.view', 'label' => 'View Surveys', 'group_id' => $surveyGroup->id],
            ['name' => 'hcm.engagement.survey.create', 'label' => 'Create Surveys', 'group_id' => $surveyGroup->id],
            ['name' => 'hcm.engagement.survey.edit', 'label' => 'Edit Surveys', 'group_id' => $surveyGroup->id],
            ['name' => 'hcm.engagement.survey.publish', 'label' => 'Publish Surveys', 'group_id' => $surveyGroup->id],
            ['name' => 'hcm.engagement.survey.archive', 'label' => 'Archive Surveys', 'group_id' => $surveyGroup->id],
            ['name' => 'hcm.engagement.campaign.view', 'label' => 'View Survey Campaigns', 'group_id' => $surveyGroup->id],
            ['name' => 'hcm.engagement.campaign.manage', 'label' => 'Manage Survey Campaigns', 'group_id' => $surveyGroup->id],
            ['name' => 'hcm.engagement.results.view', 'label' => 'View Aggregate Survey Results', 'group_id' => $surveyGroup->id],
            ['name' => 'hcm.engagement.results.export', 'label' => 'Export Aggregate Survey Results', 'group_id' => $surveyGroup->id],
            ['name' => 'hcm.engagement.confidential.view', 'label' => 'View Confidential Survey Metadata', 'group_id' => $surveyGroup->id],
            ['name' => 'hcm.engagement.anonymous.aggregate.view', 'label' => 'View Anonymous Aggregates', 'group_id' => $surveyGroup->id],

            // Action Plans
            ['name' => 'hcm.engagement.action_plan.view', 'label' => 'View Action Plans', 'group_id' => $engagementGroup->id],
            ['name' => 'hcm.engagement.action_plan.manage', 'label' => 'Manage Action Plans', 'group_id' => $engagementGroup->id],

            // Suggestions
            ['name' => 'hcm.engagement.suggestion.view', 'label' => 'View Suggestions', 'group_id' => $engagementGroup->id],
            ['name' => 'hcm.engagement.suggestion.manage', 'label' => 'Manage & Review Suggestions', 'group_id' => $engagementGroup->id],

            // Recognition & Culture
            ['name' => 'hcm.engagement.recognition.view', 'label' => 'View Recognition Wall', 'group_id' => $recognitionGroup->id],
            ['name' => 'hcm.engagement.recognition.manage', 'label' => 'Manage Recognition', 'group_id' => $recognitionGroup->id],
            ['name' => 'hcm.engagement.recognition.moderate', 'label' => 'Moderate Recognition Posts', 'group_id' => $recognitionGroup->id],
            ['name' => 'hcm.engagement.culture.view', 'label' => 'View Culture Initiatives', 'group_id' => $recognitionGroup->id],
            ['name' => 'hcm.engagement.culture.manage', 'label' => 'Manage Culture Initiatives', 'group_id' => $recognitionGroup->id],
        ];

        foreach ($permissions as $p) {
            Permission::query()->updateOrCreate(
                ['name' => $p['name']],
                [
                    'permission_group_id' => $p['group_id'],
                    'label' => $p['label'],
                ]
            );
        }
    }
}
