<?php

namespace App\Domains\OrganizationDesign\Services;

use App\Domains\Career\Models\CareerSkill;
use App\Domains\OrganizationDesign\Models\JobProfile;
use App\Domains\OrganizationDesign\Models\JobProfileCompetency;
use App\Domains\OrganizationDesign\Models\JobProfileSkill;
use App\Domains\OrganizationDesign\Models\JobProfileVersion;
use App\Domains\Performance\Models\Competency;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class JobProfileService
{
    /**
     * Create a new Job Profile and initial version snapshot.
     */
    public function createProfile(string $tenantId, array $data, ?User $actor = null): JobProfile
    {
        return DB::transaction(function () use ($tenantId, $data, $actor) {
            $profile = JobProfile::create([
                'tenant_id' => $tenantId,
                'job_id' => $data['job_id'] ?? null,
                'job_family_id' => $data['job_family_id'],
                'job_sub_family_id' => $data['job_sub_family_id'] ?? null,
                'career_track_id' => $data['career_track_id'] ?? null,
                'career_level_id' => $data['career_level_id'] ?? null,
                'job_level_id' => $data['job_level_id'] ?? null,
                'job_grade_id' => $data['job_grade_id'] ?? null,
                'code' => strtoupper($data['code']),
                'title' => $data['title'],
                'summary' => $data['summary'] ?? null,
                'responsibilities' => $data['responsibilities'] ?? [],
                'requirements' => $data['requirements'] ?? [],
                'education_requirement' => $data['education_requirement'] ?? null,
                'experience_years_min' => $data['experience_years_min'] ?? 0,
                'certifications' => $data['certifications'] ?? [],
                'travel_requirement' => $data['travel_requirement'] ?? 'none',
                'remote_eligibility' => $data['remote_eligibility'] ?? 'hybrid',
                'status' => $data['status'] ?? 'draft',
                'current_version' => 1,
            ]);

            // Create initial version snapshot
            JobProfileVersion::create([
                'tenant_id' => $tenantId,
                'job_profile_id' => $profile->id,
                'version_number' => 1,
                'snapshot_data' => $profile->toArray(),
                'change_summary' => 'Initial profile definition',
                'created_by_user_id' => $actor?->id,
            ]);

            return $profile;
        });
    }

    /**
     * Attach required or preferred skills to a job profile.
     */
    public function attachSkill(JobProfile $profile, CareerSkill $skill, array $attributes = []): JobProfileSkill
    {
        return JobProfileSkill::create([
            'tenant_id' => $profile->tenant_id,
            'job_profile_id' => $profile->id,
            'career_skill_id' => $skill->id,
            'is_required' => $attributes['is_required'] ?? true,
            'target_proficiency' => $attributes['target_proficiency'] ?? 'intermediate',
            'criticality' => $attributes['criticality'] ?? 'medium',
        ]);
    }

    /**
     * Attach competency to a job profile.
     */
    public function attachCompetency(JobProfile $profile, Competency $competency, array $attributes = []): JobProfileCompetency
    {
        return JobProfileCompetency::create([
            'tenant_id' => $profile->tenant_id,
            'job_profile_id' => $profile->id,
            'competency_id' => $competency->id,
            'competency_level_id' => $attributes['competency_level_id'] ?? null,
            'is_required' => $attributes['is_required'] ?? true,
            'criticality' => $attributes['criticality'] ?? 'medium',
        ]);
    }

    /**
     * Update job profile and create a version snapshot.
     */
    public function updateProfile(JobProfile $profile, array $data, ?User $actor = null, string $changeSummary = 'Updated profile details'): JobProfile
    {
        return DB::transaction(function () use ($profile, $data, $actor, $changeSummary) {
            $nextVersion = $profile->current_version + 1;

            $profile->update(array_merge($data, [
                'current_version' => $nextVersion,
            ]));

            JobProfileVersion::create([
                'tenant_id' => $profile->tenant_id,
                'job_profile_id' => $profile->id,
                'version_number' => $nextVersion,
                'snapshot_data' => $profile->fresh()->toArray(),
                'change_summary' => $changeSummary,
                'created_by_user_id' => $actor?->id,
            ]);

            return $profile;
        });
    }

    /**
     * Transition status (e.g. draft -> review -> approved -> published -> retired).
     */
    public function transitionStatus(JobProfile $profile, string $targetStatus, ?User $actor = null): JobProfile
    {
        $profile->update([
            'status' => $targetStatus,
        ]);

        return $profile;
    }
}
