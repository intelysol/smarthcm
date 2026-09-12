<?php

namespace Database\Seeders;

use App\Domains\Lifecycle\Models\PersonnelActionType;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Seeder;

class LifecycleDefaultActionTypesSeeder extends Seeder
{
    public function run(): void
    {
        $tenants = Tenant::all();

        $actionTypes = [
            ['code' => 'PROMOTION', 'name' => 'Promotion & Grade Elevation', 'category' => 'promotion', 'requires_approval' => true, 'requires_acknowledgement' => true],
            ['code' => 'TRANSFER', 'name' => 'Department & Location Transfer', 'category' => 'transfer', 'requires_approval' => true, 'requires_acknowledgement' => true],
            ['code' => 'JOB_CHANGE', 'name' => 'Job Title & Position Change', 'category' => 'job_change', 'requires_approval' => true, 'requires_acknowledgement' => false],
            ['code' => 'COMPENSATION_CHANGE', 'name' => 'Salary Revision & Allowance Adjustment', 'category' => 'compensation', 'requires_approval' => true, 'requires_acknowledgement' => true],
            ['code' => 'TEMPORARY_ASSIGNMENT', 'name' => 'Temporary / Acting / Secondment Assignment', 'category' => 'assignment', 'requires_approval' => true, 'requires_acknowledgement' => true],
            ['code' => 'PROBATION_COMPLETION', 'name' => 'Probation Confirmation', 'category' => 'probation', 'requires_approval' => true, 'requires_acknowledgement' => true],
        ];

        foreach ($tenants as $tenant) {
            foreach ($actionTypes as $typeData) {
                PersonnelActionType::firstOrCreate(
                    [
                        'tenant_id' => $tenant->id,
                        'code' => $typeData['code'],
                    ],
                    array_merge($typeData, ['is_active' => true])
                );
            }
        }
    }
}
