<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domains\Shared\Models\Permission;
use App\Domains\Shared\Models\PermissionGroup;
use Illuminate\Database\Seeder;

class HealthSafetyPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $group = PermissionGroup::firstOrCreate(
            ['name' => 'health_safety'],
            ['label' => 'Health, Safety & Medical Compliance']
        );

        $permissions = [
            'hcm.health.view' => 'View employee health requirements and operational restrictions',
            'hcm.health.manage_requirements' => 'Create and configure occupational health requirements',
            'hcm.health.schedule_assessment' => 'Schedule and manage employee medical assessments',
            'hcm.health.view_clinical_data' => 'View sensitive medical notes, diagnoses, and clinic findings (Medical Officers only)',
            'hcm.health.determine_fitness' => 'Issue official fit-for-work certificates and clearance records',
            'hcm.health.manage_restrictions' => 'Create, update, and manage workplace medical restrictions',
            'hcm.health.manage_rtw' => 'Create, track, and complete Return-to-Work cases and phased plans',
            'hcm.health.request_accommodation' => 'Request job or ergonomic workplace accommodations',
            'hcm.health.manage_accommodations' => 'Approve, budget, and implement workplace accommodations',
            'hcm.safety.report_incident' => 'Report safety incidents, near-misses, injuries, and hazards',
            'hcm.safety.view_incidents' => 'View occupational safety incident records and logs',
            'hcm.safety.investigate' => 'Lead and conduct root-cause safety incident investigations',
            'hcm.safety.manage_actions' => 'Create, assign, verify and close corrective/preventive actions (CAPA)',
            'hcm.safety.record_exposure' => 'Log industrial hygiene and workplace hazardous exposure data',
            'hcm.health.view_reports' => 'View EHS dashboard, OSHA Form 300 logs, TRIR, and safety KPIs',
            'hcm.health.ai_advisory' => 'Query AI advisory for incident classification, root causes, and accommodations',
        ];

        foreach ($permissions as $name => $description) {
            Permission::firstOrCreate(
                ['name' => $name],
                [
                    'permission_group_id' => $group->id,
                    'uuid' => (string) \Illuminate\Support\Str::uuid(),
                    'label' => $description,
                ]
            );
        }
    }
}

