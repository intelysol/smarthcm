<?php

namespace Tests\Feature\EmployeeProfile;

use App\Domains\Shared\Models\Permission;
use App\Domains\Shared\Models\Tenant;
use Database\Seeders\EmployeeProfilePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeProfileSeedersTest extends TestCase
{
    use RefreshDatabase;

    public function test_permission_seeder_execution(): void
    {
        $tenant = Tenant::factory()->create();

        $this->seed(EmployeeProfilePermissionSeeder::class);

        $this->assertTrue(Permission::where('name', 'employee_profile.view')->exists());
        $this->assertTrue(Permission::where('name', 'employee_profile.directory')->exists());
        $this->assertTrue(Permission::where('name', 'employee_profile.org_chart')->exists());
        $this->assertTrue(Permission::where('name', 'employee_profile.people_search')->exists());
        $this->assertTrue(Permission::where('name', 'employee_profile.change_request')->exists());
        $this->assertTrue(Permission::where('name', 'employee_profile.approve_change')->exists());
        $this->assertTrue(Permission::where('name', 'employee_profile.view_sensitive')->exists());
        $this->assertTrue(Permission::where('name', 'employee_profile.ai_search')->exists());
    }
}
