<?php

namespace Tests\Feature\OrganizationDesign;

use App\Domains\OrganizationDesign\Models\CareerTrack;
use App\Domains\OrganizationDesign\Models\JobFamily;
use App\Domains\OrganizationDesign\Services\JobArchitectureService;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobArchitectureAndHierarchyTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_family_and_sub_family_hierarchy(): void
    {
        $tenant = Tenant::factory()->create();
        $service = app(JobArchitectureService::class);

        $family = $service->createFamily($tenant->id, [
            'code' => 'TECH',
            'name' => 'Technology & Engineering',
            'description' => 'Software engineering and IT disciplines',
        ]);

        $this->assertDatabaseHas('job_families', [
            'id' => $family->id,
            'code' => 'TECH',
            'name' => 'Technology & Engineering',
        ]);

        $subFamily = $service->createSubFamily($family, [
            'code' => 'SWE',
            'name' => 'Software Engineering',
            'description' => 'Backend, frontend, and full-stack software development',
        ]);

        $this->assertDatabaseHas('job_sub_families', [
            'id' => $subFamily->id,
            'job_family_id' => $family->id,
            'code' => 'SWE',
            'name' => 'Software Engineering',
        ]);

        $this->assertCount(1, $family->subFamilies);
        $this->assertEquals('SWE', $family->subFamilies->first()->code);
    }

    public function test_career_tracks_and_levels_and_job_levels(): void
    {
        $tenant = Tenant::factory()->create();
        $service = app(JobArchitectureService::class);

        // 1. Career Track
        $track = $service->createCareerTrack($tenant->id, [
            'code' => 'IC',
            'name' => 'Individual Contributor',
            'track_type' => 'individual_contributor',
        ]);

        $this->assertDatabaseHas('career_tracks', [
            'id' => $track->id,
            'code' => 'IC',
            'track_type' => 'individual_contributor',
        ]);

        // 2. Career Levels (L1, L2, L3)
        $level1 = $service->addCareerLevel($track, [
            'level_code' => 'L1',
            'name' => 'Associate / Entry',
            'rank_order' => 1,
            'typical_experience_years' => 1.0,
        ]);

        $level2 = $service->addCareerLevel($track, [
            'level_code' => 'L2',
            'name' => 'Professional / Mid',
            'rank_order' => 2,
            'typical_experience_years' => 3.0,
        ]);

        $this->assertCount(2, $track->careerLevels);

        // 3. Job Levels
        $jobLevel = $service->createJobLevel($tenant->id, [
            'code' => 'SENIOR',
            'name' => 'Senior Professional',
            'numerical_level' => 3,
        ]);

        $this->assertDatabaseHas('job_levels', [
            'id' => $jobLevel->id,
            'code' => 'SENIOR',
            'numerical_level' => 3,
        ]);

        // 4. Complete Tree
        $tree = $service->getArchitectureTree($tenant->id);
        $this->assertArrayHasKey('families', $tree);
        $this->assertArrayHasKey('career_tracks', $tree);
        $this->assertArrayHasKey('job_levels', $tree);
    }
}
