<?php

namespace Tests\Feature\Offboarding;

use App\Domains\Offboarding\Models\SeparationType;
use App\Domains\Shared\Models\Permission;
use App\Domains\Shared\Models\Tenant;
use Database\Seeders\OffboardingDefaultSeparationTypesSeeder;
use Database\Seeders\OffboardingPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OffboardingSeedersTest extends TestCase
{
    use RefreshDatabase;

    public function test_offboarding_seeders_execution(): void
    {
        $tenant = Tenant::factory()->create();

        // 1. Run Permissions Seeder
        $this->seed(OffboardingPermissionSeeder::class);
        $this->assertTrue(Permission::where('name', 'separations.view')->exists());
        $this->assertTrue(Permission::where('name', 'separations.create')->exists());
        $this->assertTrue(Permission::where('name', 'separations.approve')->exists());
        $this->assertTrue(Permission::where('name', 'separations.terminate')->exists());
        $this->assertTrue(Permission::where('name', 'separations.notice_override')->exists());
        $this->assertTrue(Permission::where('name', 'separations.clearance_manage')->exists());
        $this->assertTrue(Permission::where('name', 'separations.clearance_waive')->exists());
        $this->assertTrue(Permission::where('name', 'separations.settlement_approve')->exists());
        $this->assertTrue(Permission::where('name', 'separations.reverse')->exists());
        $this->assertTrue(Permission::where('name', 'separations.reinstate')->exists());
        $this->assertTrue(Permission::where('name', 'separations.self.view')->exists());

        // 2. Run Default Separation Types Seeder
        $this->seed(OffboardingDefaultSeparationTypesSeeder::class);
        $this->assertTrue(SeparationType::where('tenant_id', $tenant->id)->where('code', 'RESIGNATION')->exists());
        $this->assertTrue(SeparationType::where('tenant_id', $tenant->id)->where('code', 'INVOLUNTARY_TERMINATION')->exists());
        $this->assertTrue(SeparationType::where('tenant_id', $tenant->id)->where('code', 'RETIREMENT')->exists());
        $this->assertTrue(SeparationType::where('tenant_id', $tenant->id)->where('code', 'CONTRACT_EXPIRY')->exists());
        $this->assertTrue(SeparationType::where('tenant_id', $tenant->id)->where('code', 'MUTUAL_SEPARATION')->exists());
        $this->assertTrue(SeparationType::where('tenant_id', $tenant->id)->where('code', 'REDUNDANCY')->exists());
    }
}
