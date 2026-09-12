<?php

namespace Tests\Feature\Compliance;

use App\Domains\Shared\Models\Tenant;
use App\Domains\Shared\Models\Permission;
use App\Domains\Shared\Models\PermissionGroup;
use Database\Seeders\ComplianceDefaultRequirementTypesSeeder;
use Database\Seeders\CompliancePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompliancePermissionSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeders_populate_permissions_and_default_types(): void
    {
        $tenant = Tenant::factory()->create();

        // 1. Run Permission Seeder
        $this->seed(CompliancePermissionSeeder::class);

        $this->assertTrue(Permission::where('name', 'compliance.view')->exists());
        $this->assertTrue(Permission::where('name', 'compliance.exempt.approve')->exists());
        $this->assertTrue(Permission::where('name', 'compliance.work_permit.manage')->exists());

        // 2. Run Requirement Types Seeder
        $this->seed(ComplianceDefaultRequirementTypesSeeder::class);

        $this->assertDatabaseHas('hcm_compliance_requirement_types', [
            'code' => 'work_permit',
        ]);
        $this->assertDatabaseHas('hcm_compliance_requirement_types', [
            'code' => 'visa',
        ]);
        $this->assertDatabaseHas('hcm_compliance_requirement_types', [
            'code' => 'professional_license',
        ]);
        $this->assertDatabaseHas('hcm_compliance_requirement_types', [
            'code' => 'government_registration',
        ]);
    }
}
