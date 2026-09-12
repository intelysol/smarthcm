<?php

namespace Database\Seeders;

use App\Domains\Shared\Models\Permission;
use App\Domains\Shared\Models\PermissionGroup;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PersonalDataPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $group = PermissionGroup::firstOrCreate(
            ['name' => 'personal_data'],
            ['label' => 'Employee Personal Data & Master Governance']
        );

        $permissions = [
            'personal_data.view' => 'View employee personal data',
            'personal_data.manage' => 'Manage employee personal data',
            'personal_data.view_sensitive' => 'View sensitive unmasked identifiers and bank accounts',
            'personal_data.manage_sensitive' => 'Manage sensitive personal records',
            'personal_data.address.manage' => 'Manage employee address records',
            'personal_data.emergency_contact.manage' => 'Manage employee emergency contacts',
            'personal_data.dependent.manage' => 'Manage employee dependents and family profiles',
            'personal_data.identifier.manage' => 'Manage employee identifiers and passports',
            'personal_data.bank_request.submit' => 'Submit employee bank detail change requests',
            'personal_data.bank_request.approve' => 'Review and approve bank detail change requests',
            'personal_data.change_request.manage' => 'Review and process personal data change requests',
            'personal_data.verification.manage' => 'Perform employee data and document verifications',
            'personal_data.quality.view' => 'View employee data quality scores and audit issues',
            'personal_data.quality.recalculate' => 'Recalculate employee data quality ratings',
            'personal_data.bulk.manage' => 'Perform bulk personal data uploads and dry runs',
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
