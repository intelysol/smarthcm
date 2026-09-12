<?php

namespace Database\Seeders;

use App\Domains\Shared\Models\Permission;
use App\Domains\Shared\Models\PermissionGroup;
use Illuminate\Database\Seeder;

class EmployeeRelationsPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $erGroup = PermissionGroup::query()->firstOrCreate(
            ['name' => 'employee_relations'],
            ['label' => 'Employee Relations & HR Case Management']
        );

        $investigationGroup = PermissionGroup::query()->firstOrCreate(
            ['name' => 'er_investigations'],
            ['label' => 'ER Investigations & Hearings']
        );

        $legalGroup = PermissionGroup::query()->firstOrCreate(
            ['name' => 'er_legal_governance'],
            ['label' => 'ER Legal & Governance']
        );

        $permissions = [
            // General ER
            ['name' => 'hcm.employee_relations.view', 'label' => 'View Employee Relations Module', 'group_id' => $erGroup->id],
            ['name' => 'hcm.employee_relations.create', 'label' => 'Create ER Case Intake', 'group_id' => $erGroup->id],
            ['name' => 'hcm.employee_relations.manage', 'label' => 'Manage Employee Relations Platform', 'group_id' => $erGroup->id],

            // Cases
            ['name' => 'hcm.employee_relations.case.view', 'label' => 'View ER Cases', 'group_id' => $erGroup->id],
            ['name' => 'hcm.employee_relations.case.create', 'label' => 'Create Case Directly', 'group_id' => $erGroup->id],
            ['name' => 'hcm.employee_relations.case.edit', 'label' => 'Edit ER Cases', 'group_id' => $erGroup->id],
            ['name' => 'hcm.employee_relations.case.assign', 'label' => 'Assign ER Cases & Roles', 'group_id' => $erGroup->id],
            ['name' => 'hcm.employee_relations.case.close', 'label' => 'Close ER Cases', 'group_id' => $erGroup->id],
            ['name' => 'hcm.employee_relations.case.reopen', 'label' => 'Reopen ER Cases', 'group_id' => $erGroup->id],

            // Investigations
            ['name' => 'hcm.employee_relations.investigation.view', 'label' => 'View Case Investigations', 'group_id' => $investigationGroup->id],
            ['name' => 'hcm.employee_relations.investigation.manage', 'label' => 'Manage & Conduct Investigations', 'group_id' => $investigationGroup->id],

            // Evidence
            ['name' => 'hcm.employee_relations.evidence.view', 'label' => 'View Case Evidence', 'group_id' => $investigationGroup->id],
            ['name' => 'hcm.employee_relations.evidence.manage', 'label' => 'Manage Case Evidence', 'group_id' => $investigationGroup->id],
            ['name' => 'hcm.employee_relations.evidence.download', 'label' => 'Download Case Evidence Files', 'group_id' => $investigationGroup->id],

            // Statements & Interviews
            ['name' => 'hcm.employee_relations.statement.view', 'label' => 'View Participant Statements', 'group_id' => $investigationGroup->id],
            ['name' => 'hcm.employee_relations.statement.manage', 'label' => 'Manage & Record Statements', 'group_id' => $investigationGroup->id],
            ['name' => 'hcm.employee_relations.interview.view', 'label' => 'View Case Interviews', 'group_id' => $investigationGroup->id],
            ['name' => 'hcm.employee_relations.interview.manage', 'label' => 'Conduct & Record Interviews', 'group_id' => $investigationGroup->id],

            // Hearings
            ['name' => 'hcm.employee_relations.hearing.view', 'label' => 'View Case Hearings', 'group_id' => $investigationGroup->id],
            ['name' => 'hcm.employee_relations.hearing.manage', 'label' => 'Chair & Manage Hearings', 'group_id' => $investigationGroup->id],

            // Findings & Decisions
            ['name' => 'hcm.employee_relations.finding.view', 'label' => 'View Allegation Findings', 'group_id' => $investigationGroup->id],
            ['name' => 'hcm.employee_relations.finding.manage', 'label' => 'Record Findings on Allegations', 'group_id' => $investigationGroup->id],
            ['name' => 'hcm.employee_relations.decision.view', 'label' => 'View Formal Case Decisions', 'group_id' => $erGroup->id],
            ['name' => 'hcm.employee_relations.decision.manage', 'label' => 'Record Disciplinary & Case Decisions', 'group_id' => $erGroup->id],

            // Actions & Appeals
            ['name' => 'hcm.employee_relations.action.view', 'label' => 'View Corrective Actions', 'group_id' => $erGroup->id],
            ['name' => 'hcm.employee_relations.action.manage', 'label' => 'Assign & Verify Corrective Actions', 'group_id' => $erGroup->id],
            ['name' => 'hcm.employee_relations.appeal.view', 'label' => 'View Case Appeals', 'group_id' => $erGroup->id],
            ['name' => 'hcm.employee_relations.appeal.manage', 'label' => 'Review & Decide Appeals', 'group_id' => $erGroup->id],

            // Legal & Governance
            ['name' => 'hcm.employee_relations.legal.view', 'label' => 'View Legal Notes & Advices', 'group_id' => $legalGroup->id],
            ['name' => 'hcm.employee_relations.legal.manage', 'label' => 'Manage Legal Reviews & Records', 'group_id' => $legalGroup->id],
            ['name' => 'hcm.employee_relations.retention.manage', 'label' => 'Manage Retention Policies & Controlled Disposal', 'group_id' => $legalGroup->id],
            ['name' => 'hcm.employee_relations.legal_hold.manage', 'label' => 'Place & Release Legal Holds', 'group_id' => $legalGroup->id],

            // Reports & Analytics
            ['name' => 'hcm.employee_relations.report.view', 'label' => 'View ER Case Reports & Analytics', 'group_id' => $erGroup->id],
            ['name' => 'hcm.employee_relations.report.export', 'label' => 'Export ER Case Data & Bundles', 'group_id' => $erGroup->id],

            // Confidentiality Special Permissions
            ['name' => 'hcm.employee_relations.confidential.view', 'label' => 'View Highly Confidential Cases', 'group_id' => $legalGroup->id],
            ['name' => 'hcm.employee_relations.restricted.view', 'label' => 'View Legal Restricted Cases & Notes', 'group_id' => $legalGroup->id],
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
