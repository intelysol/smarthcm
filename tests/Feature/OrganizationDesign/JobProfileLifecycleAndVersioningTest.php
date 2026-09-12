<?php

namespace Tests\Feature\OrganizationDesign;

use App\Domains\Career\Models\CareerSkill;
use App\Domains\Career\Models\CareerSkillCategory;
use App\Domains\OrganizationDesign\Models\CareerLevel;
use App\Domains\OrganizationDesign\Models\CareerTrack;
use App\Domains\OrganizationDesign\Models\JobFamily;
use App\Domains\OrganizationDesign\Models\JobLevel;
use App\Domains\OrganizationDesign\Models\JobProfile;
use App\Domains\OrganizationDesign\Services\JobProfileService;
use App\Domains\Performance\Models\Competency;
use App\Domains\Performance\Models\CompetencyCategory;
use App\Domains\Performance\Models\CompetencyFramework;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobProfileLifecycleAndVersioningTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_profile_creation_versioning_and_requirements(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $family = JobFamily::create([
            'tenant_id' => $tenant->id,
            'code' => 'ENG',
            'name' => 'Engineering',
        ]);

        $track = CareerTrack::create([
            'tenant_id' => $tenant->id,
            'code' => 'IC',
            'name' => 'Individual Contributor',
        ]);

        $careerLevel = CareerLevel::create([
            'tenant_id' => $tenant->id,
            'career_track_id' => $track->id,
            'level_code' => 'L3',
            'name' => 'Senior Engineer',
            'rank_order' => 3,
        ]);

        $jobLevel = JobLevel::create([
            'tenant_id' => $tenant->id,
            'code' => 'SENIOR',
            'name' => 'Senior',
            'numerical_level' => 3,
        ]);

        $skillCategory = CareerSkillCategory::create([
            'tenant_id' => $tenant->id,
            'code' => 'TECH',
            'name' => 'Technical Skills',
        ]);

        $skill = CareerSkill::create([
            'tenant_id' => $tenant->id,
            'category_id' => $skillCategory->id,
            'code' => 'PHP',
            'name' => 'PHP & Laravel',
        ]);

        $framework = CompetencyFramework::create([
            'tenant_id' => $tenant->id,
            'name' => 'Core Values',
        ]);

        $compCategory = CompetencyCategory::create([
            'tenant_id' => $tenant->id,
            'framework_id' => $framework->id,
            'name' => 'Leadership',
        ]);

        $competency = Competency::create([
            'tenant_id' => $tenant->id,
            'category_id' => $compCategory->id,
            'name' => 'Systemic Architecture',
        ]);

        $profileService = app(JobProfileService::class);

        // 1. Create Job Profile
        $profile = $profileService->createProfile($tenant->id, [
            'job_family_id' => $family->id,
            'career_track_id' => $track->id,
            'career_level_id' => $careerLevel->id,
            'job_level_id' => $jobLevel->id,
            'code' => 'SR-SWE-01',
            'title' => 'Senior Software Engineer',
            'summary' => 'Leads development of mission critical microservices',
            'responsibilities' => ['Design scalable services', 'Mentor junior engineers'],
            'education_requirement' => 'Bachelor in Computer Science',
            'experience_years_min' => 5.0,
            'remote_eligibility' => 'remote',
            'status' => 'draft',
        ], $user);

        $this->assertEquals(1, $profile->current_version);
        $this->assertDatabaseHas('job_profile_versions', [
            'job_profile_id' => $profile->id,
            'version_number' => 1,
        ]);

        // 2. Attach Skill & Competency
        $profileService->attachSkill($profile, $skill, [
            'is_required' => true,
            'target_proficiency' => 'expert',
            'criticality' => 'high',
        ]);

        $profileService->attachCompetency($profile, $competency, [
            'is_required' => true,
            'criticality' => 'high',
        ]);

        $this->assertCount(1, $profile->skills);
        $this->assertCount(1, $profile->competencies);

        // 3. Update Profile creates Version 2 snapshot
        $profileService->updateProfile($profile, [
            'summary' => 'Updated: Leads development and resilience of core enterprise services',
        ], $user, 'Clarified scope of microservices resilience');

        $profile->refresh();
        $this->assertEquals(2, $profile->current_version);
        $this->assertDatabaseHas('job_profile_versions', [
            'job_profile_id' => $profile->id,
            'version_number' => 2,
            'change_summary' => 'Clarified scope of microservices resilience',
        ]);

        // 4. Lifecycle transition
        $profileService->transitionStatus($profile, 'published', $user);
        $profile->refresh();
        $this->assertEquals('published', $profile->status);
    }
}
