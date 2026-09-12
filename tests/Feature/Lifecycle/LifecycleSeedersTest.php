<?php

namespace Tests\Feature\Lifecycle;

use App\Domains\Lifecycle\Models\PersonnelActionType;
use App\Domains\Shared\Models\Permission;
use App\Domains\Shared\Models\Tenant;
use Database\Seeders\LifecycleDefaultActionTypesSeeder;
use Database\Seeders\LifecyclePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LifecycleSeedersTest extends TestCase
{
    use RefreshDatabase;

    public function test_lifecycle_seeders_execution(): void
    {
        $tenant = Tenant::factory()->create();

        // 1. Run Permissions Seeder
        $this->seed(LifecyclePermissionSeeder::class);
        $this->assertTrue(Permission::where('name', 'personnel_actions.view')->exists());
        $this->assertTrue(Permission::where('name', 'personnel_actions.create')->exists());
        $this->assertTrue(Permission::where('name', 'personnel_actions.approve')->exists());
        $this->assertTrue(Permission::where('name', 'personnel_actions.execute')->exists());
        $this->assertTrue(Permission::where('name', 'personnel_actions.reverse')->exists());
        $this->assertTrue(Permission::where('name', 'personnel_actions.bulk')->exists());
        $this->assertTrue(Permission::where('name', 'personnel_actions.backdate')->exists());
        $this->assertTrue(Permission::where('name', 'personnel_actions.self.view')->exists());
        $this->assertTrue(Permission::where('name', 'personnel_actions.self.acknowledge')->exists());

        // 2. Run Default Action Types Seeder
        $this->seed(LifecycleDefaultActionTypesSeeder::class);
        $this->assertTrue(PersonnelActionType::where('tenant_id', $tenant->id)->where('code', 'PROMOTION')->exists());
        $this->assertTrue(PersonnelActionType::where('tenant_id', $tenant->id)->where('code', 'TRANSFER')->exists());
        $this->assertTrue(PersonnelActionType::where('tenant_id', $tenant->id)->where('code', 'COMPENSATION_CHANGE')->exists());
        $this->assertTrue(PersonnelActionType::where('tenant_id', $tenant->id)->where('code', 'TEMPORARY_ASSIGNMENT')->exists());
    }
}
