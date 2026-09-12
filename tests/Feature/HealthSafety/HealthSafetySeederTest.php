<?php

declare(strict_types=1);

namespace Tests\Feature\HealthSafety;

use App\Domains\HealthSafety\Models\HcmHealthRequirementType;
use App\Domains\Shared\Models\Permission;
use Database\Seeders\HealthDefaultRequirementTypesSeeder;
use Database\Seeders\HealthSafetyPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HealthSafetySeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_safety_seeders_run_successfully(): void
    {
        $this->seed(HealthSafetyPermissionSeeder::class);
        $this->seed(HealthDefaultRequirementTypesSeeder::class);

        $this->assertDatabaseHas('permissions', [
            'name' => 'hcm.health.view_clinical_data',
        ]);
        $this->assertDatabaseHas('permissions', [
            'name' => 'hcm.safety.report_incident',
        ]);

        $this->assertDatabaseHas('hcm_health_requirement_types', [
            'code' => 'pre_placement_physical',
        ]);
        $this->assertDatabaseHas('hcm_health_requirement_types', [
            'code' => 'audiometric_testing',
        ]);
    }
}
