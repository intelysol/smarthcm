<?php

namespace Database\Seeders;

use App\Domains\Shared\Models\Permission;
use App\Domains\Shared\Models\PermissionGroup;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CompliancePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $group = PermissionGroup::firstOrCreate(
            ['name' => 'compliance'],
            ['label' => 'Workforce Compliance & Regulatory Management']
        );

        $permissions = [
            'compliance.view' => 'View employee compliance records and statuses',
            'compliance.manage' => 'Manage employee compliance requirements and assignments',
            'compliance.requirements.manage' => 'Configure and version compliance requirements',
            'compliance.work_permit.manage' => 'Manage employee work permit records',
            'compliance.visa.manage' => 'Manage employee visas and residency permits',
            'compliance.license.manage' => 'Manage professional and occupational licenses',
            'compliance.registration.manage' => 'Manage mandatory government registrations',
            'compliance.verify' => 'Perform compliance record verifications',
            'compliance.renew' => 'Initiate and manage compliance renewal workflows',
            'compliance.exempt.request' => 'Submit compliance waiver or exemption requests',
            'compliance.exempt.approve' => 'Review and approve compliance exemptions',
            'compliance.dashboard.view' => 'Access workforce compliance dashboards',
            'compliance.reports.view' => 'Generate and export regulatory compliance reports',
            'compliance.bulk.manage' => 'Perform bulk compliance assignments and uploads',
            'compliance.escalations.manage' => 'Manage compliance expiration escalation alerts',
            'compliance.ai.view' => 'View AI-assisted compliance analysis and insights',
        ];

        foreach ($permissions as $name => $description) {
            Permission::firstOrCreate(
                ['name' => $name],
                [
                    'permission_group_id' => $group->id,
                    'uuid' => (string) Str::uuid(),
                    'label' => $description,
                ]
            );
        }
    }
}
