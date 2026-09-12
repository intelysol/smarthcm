<?php

namespace Database\Seeders;

use App\Domains\Organization\Models\EmploymentType;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Seeder;

class OrganizationMasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $types = ['Permanent', 'Contract', 'Probation', 'Temporary', 'Internship', 'Consultant', 'Part-Time'];

        Tenant::query()->each(function (Tenant $tenant) use ($types): void {
            foreach ($types as $type) {
                EmploymentType::query()->firstOrCreate(
                    ['tenant_id' => $tenant->id, 'name' => $type],
                    ['status' => 'active'],
                );
            }
        });
    }
}
