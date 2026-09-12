<?php

namespace Database\Seeders;

use App\Domains\Compliance\Models\HcmComplianceRequirementType;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Seeder;

class ComplianceDefaultRequirementTypesSeeder extends Seeder
{
    public function run(): void
    {
        $tenantIds = Tenant::pluck('id')->toArray();
        if (empty($tenantIds)) {
            $tenantIds = ['default'];
        }

        $types = [
            [
                'code' => 'work_permit',
                'name' => 'Work Permit',
                'description' => 'Official government authorization for non-citizens to engage in gainful employment.',
                'default_renewal_required' => true,
                'default_grace_period_days' => 30,
            ],
            [
                'code' => 'visa',
                'name' => 'Visa / Residency Permit',
                'description' => 'Immigration document granting legal entry, residency or stay in jurisdiction.',
                'default_renewal_required' => true,
                'default_grace_period_days' => 15,
            ],
            [
                'code' => 'professional_license',
                'name' => 'Professional License',
                'description' => 'Credential issued by a governing licensing board required for specialized practice.',
                'default_renewal_required' => true,
                'default_grace_period_days' => 60,
            ],
            [
                'code' => 'occupational_license',
                'name' => 'Occupational License',
                'description' => 'Trade or vocational permit necessary to perform specific job duties.',
                'default_renewal_required' => true,
                'default_grace_period_days' => 30,
            ],
            [
                'code' => 'government_registration',
                'name' => 'Government Registration',
                'description' => 'Mandatory statutory or tax registration with local governmental authority.',
                'default_renewal_required' => false,
                'default_grace_period_days' => 0,
            ],
            [
                'code' => 'safety_certification',
                'name' => 'Safety Certification',
                'description' => 'Mandatory environmental, health, or workplace safety qualification.',
                'default_renewal_required' => true,
                'default_grace_period_days' => 14,
            ],
            [
                'code' => 'mandatory_training',
                'name' => 'Mandatory Training',
                'description' => 'Statutory training requirement typically completed via learning management.',
                'default_renewal_required' => true,
                'default_grace_period_days' => 30,
            ],
            [
                'code' => 'background_check',
                'name' => 'Background Check Clearance',
                'description' => 'Pre-employment or periodic criminal/financial background investigation clearance.',
                'default_renewal_required' => false,
                'default_grace_period_days' => 0,
            ],
            [
                'code' => 'medical_clearance',
                'name' => 'Medical Clearance',
                'description' => 'Occupational health assessment confirming fitness for specified duties.',
                'default_renewal_required' => true,
                'default_grace_period_days' => 30,
            ],
        ];

        foreach ($tenantIds as $tId) {
            foreach ($types as $t) {
                HcmComplianceRequirementType::firstOrCreate(
                    ['tenant_id' => $tId, 'code' => $t['code']],
                    array_merge($t, ['tenant_id' => $tId, 'is_active' => true])
                );
            }
        }
    }
}
