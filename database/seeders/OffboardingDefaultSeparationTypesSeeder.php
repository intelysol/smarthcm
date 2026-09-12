<?php

namespace Database\Seeders;

use App\Domains\Offboarding\Models\SeparationType;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Seeder;

class OffboardingDefaultSeparationTypesSeeder extends Seeder
{
    public function run(): void
    {
        $tenants = Tenant::all();

        $types = [
            ['code' => 'RESIGNATION', 'name' => 'Voluntary Resignation', 'category' => 'voluntary', 'notice_days_default' => 30, 'requires_clearance' => true, 'requires_exit_interview' => true],
            ['code' => 'INVOLUNTARY_TERMINATION', 'name' => 'Involuntary Termination', 'category' => 'involuntary', 'notice_days_default' => 0, 'requires_clearance' => true, 'requires_exit_interview' => false],
            ['code' => 'RETIREMENT', 'name' => 'Retirement', 'category' => 'retirement', 'notice_days_default' => 60, 'requires_clearance' => true, 'requires_exit_interview' => true],
            ['code' => 'CONTRACT_EXPIRY', 'name' => 'End of Fixed-Term Contract', 'category' => 'expiry', 'notice_days_default' => 30, 'requires_clearance' => true, 'requires_exit_interview' => true],
            ['code' => 'MUTUAL_SEPARATION', 'name' => 'Mutual Separation Agreement', 'category' => 'mutual', 'notice_days_default' => 15, 'requires_clearance' => true, 'requires_exit_interview' => true],
            ['code' => 'REDUNDANCY', 'name' => 'Redundancy / Restructuring', 'category' => 'involuntary', 'notice_days_default' => 30, 'requires_clearance' => true, 'requires_exit_interview' => false],
        ];

        foreach ($tenants as $tenant) {
            foreach ($types as $typeData) {
                SeparationType::firstOrCreate(
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
