<?php

namespace Database\Seeders;

use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(OrganizationPermissionSeeder::class);
        $this->call(EmployeePermissionSeeder::class);
        $this->call(PlatformFoundationSeeder::class);
        $this->call(MetadataPlatformSeeder::class);
        $this->call(BusinessRulesSeeder::class);
        $this->call(PerformancePermissionSeeder::class);
        $this->call(LearningPermissionSeeder::class);
        $this->call(CareerPermissionSeeder::class);
        $this->call(EngagementPermissionSeeder::class);
        $this->call(CompensationPermissionSeeder::class);

        $tenant = Tenant::factory()->create([
            'name' => 'SmartHCM Demo Tenant',
            'slug' => 'smarthcm-demo',
            'timezone' => 'Asia/Karachi',
            'currency' => 'PKR',
        ]);

        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        Company::factory()->create([
            'tenant_id' => $tenant->id,
            'name' => 'SmartHCM Demo Company',
            'legal_name' => 'SmartHCM Demo Company Pvt Ltd',
            'timezone' => 'Asia/Karachi',
            'currency' => 'PKR',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $this->call(OrganizationMasterDataSeeder::class);
        $this->call(DemoEnvironmentSeeder::class);
    }
}
