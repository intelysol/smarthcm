<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domains\HealthSafety\Models\HcmHealthRequirementType;
use Illuminate\Database\Seeder;

class HealthDefaultRequirementTypesSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'code' => 'pre_placement_physical',
                'name' => 'Pre-Placement / Pre-Employment Physical',
                'category' => 'pre_placement',
                'description' => 'Baseline physical examination to assess baseline health and job capability before job assignment.',
                'requires_medical_provider' => true,
                'requires_certificate' => true,
                'default_validity_months' => 24,
            ],
            [
                'code' => 'periodic_occupational_health',
                'name' => 'Periodic Occupational Health Surveillance',
                'category' => 'periodic_surveillance',
                'description' => 'Mandatory recurring medical surveillance based on occupational hazard profile.',
                'requires_medical_provider' => true,
                'requires_certificate' => true,
                'default_validity_months' => 12,
            ],
            [
                'code' => 'audiometric_testing',
                'name' => 'Hearing Conservation Audiogram',
                'category' => 'periodic_surveillance',
                'description' => 'Baseline and annual audiometric testing for noise-exposed personnel.',
                'requires_medical_provider' => true,
                'requires_certificate' => true,
                'default_validity_months' => 12,
            ],
            [
                'code' => 'respiratory_fit_clearance',
                'name' => 'Respiratory Protection Medical Clearance & Fit Test',
                'category' => 'regulatory_mandate',
                'description' => 'Medical clearance to wear tight-fitting respirators and qualitative/quantitative fit testing.',
                'requires_medical_provider' => true,
                'requires_certificate' => true,
                'default_validity_months' => 12,
            ],
            [
                'code' => 'post_incident_evaluation',
                'name' => 'Post-Incident Medical Assessment',
                'category' => 'post_incident',
                'description' => 'Medical evaluation following a workplace injury, hazardous substance exposure, or acute incident.',
                'requires_medical_provider' => true,
                'requires_certificate' => false,
                'default_validity_months' => null,
            ],
            [
                'code' => 'return_to_work_clearance',
                'name' => 'Fitness-for-Duty Return-to-Work Clearance',
                'category' => 'return_to_work',
                'description' => 'Evaluation of medical condition and functional work limitations following extended illness or injury.',
                'requires_medical_provider' => true,
                'requires_certificate' => true,
                'default_validity_months' => 6,
            ],
            [
                'code' => 'commercial_drivers_license_medical',
                'name' => 'DOT / Commercial Driver Medical Fitness (CDL)',
                'category' => 'regulatory_mandate',
                'description' => 'Statutory medical examination for commercial motor vehicle drivers and transport operators.',
                'requires_medical_provider' => true,
                'requires_certificate' => true,
                'default_validity_months' => 24,
            ],
            [
                'code' => 'hepatitis_b_immunization',
                'name' => 'Hepatitis B Vaccination / Titer Clearance',
                'category' => 'immunization',
                'description' => 'Immunization against bloodborne pathogens for healthcare and biological risk roles.',
                'requires_medical_provider' => false,
                'requires_certificate' => true,
                'default_validity_months' => 60,
            ],
        ];

        $tenantIds = \App\Domains\Shared\Models\Tenant::pluck('id')->toArray();
        if (empty($tenantIds)) {
            $tenantIds = ['default'];
        }

        foreach ($tenantIds as $tenantId) {
            foreach ($types as $type) {
                HcmHealthRequirementType::firstOrCreate(
                    [
                        'tenant_id' => $tenantId,
                        'code' => $type['code'],
                    ],
                    [
                        'name' => $type['name'],
                        'description' => $type['description'],
                        'default_validity_months' => $type['default_validity_months'],
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}

