<?php

namespace Database\Seeders;

use App\Domains\Shared\Models\Permission;
use App\Domains\Shared\Models\PermissionGroup;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OffboardingPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $group = PermissionGroup::firstOrCreate(
            ['name' => 'offboarding'],
            ['label' => 'Offboarding & Separation Management']
        );

        $permissions = [
            'separations.view' => 'View separation requests and offboarding details',
            'separations.create' => 'Draft new separation requests',
            'separations.update' => 'Modify draft separation requests',
            'separations.submit' => 'Submit separation requests for approval',
            'separations.approve' => 'Approve employee separation requests',
            'separations.reject' => 'Reject employee separation requests',
            'separations.cancel' => 'Cancel pending separation requests',
            'separations.withdraw' => 'Authorize resignation withdrawal',
            'separations.terminate' => 'Initiate involuntary termination and redundancy workflows',
            'separations.approve_termination' => 'Approve involuntary terminations',
            'separations.execute_termination' => 'Finalize and execute involuntary employee termination',
            'separations.notice_override' => 'Override calculated notice periods and last working day',
            'separations.clearance_manage' => 'Manage and sign off on departmental clearance items',
            'separations.clearance_waive' => 'Waive unresolved departmental clearance items',
            'separations.settlement_manage' => 'Orchestrate and update final settlement details',
            'separations.settlement_approve' => 'Authorize approved final settlement statement',
            'separations.documents_generate' => 'Generate official relieving and experience certificates',
            'separations.reverse' => 'Initiate and execute compensating separation reversal',
            'separations.reinstate' => 'Authorize and execute formal employee reinstatement',
            'separations.self.view' => 'Employee self-service and post-exit access to personal separation documents',
        ];

        foreach ($permissions as $name => $description) {
            Permission::firstOrCreate(
                ['name' => $name],
                [
                    'permission_group_id' => $group->id,
                    'uuid' => (string) Str::uuid(),
                    'label' => ucwords(str_replace(['separations.', '.'], ['', ' '], $name)),
                    'module' => 'offboarding',
                    'resource' => 'separations',
                    'action' => 'manage',
                    'description' => $description,
                    'status' => 'active',
                ]
            );
        }
    }
}
