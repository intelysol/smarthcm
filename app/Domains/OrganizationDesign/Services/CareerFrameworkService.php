<?php

namespace App\Domains\OrganizationDesign\Services;

use App\Domains\Career\Models\CareerPath;
use App\Domains\Career\Models\CareerPathStep;
use App\Domains\OrganizationDesign\Models\CareerLevel;
use App\Domains\OrganizationDesign\Models\CareerTrack;
use App\Domains\OrganizationDesign\Models\JobProfile;

class CareerFrameworkService
{
    /**
     * Get career paths and progression steps for a Job Profile or Department.
     */
    public function getCareerProgression(JobProfile $profile): array
    {
        $tenantId = $profile->tenant_id;
        $track = $profile->careerTrack;
        $currentLevel = $profile->careerLevel;

        $nextLevels = [];
        if ($track && $currentLevel) {
            $nextLevels = CareerLevel::where('tenant_id', $tenantId)
                ->where('career_track_id', $track->id)
                ->where('rank_order', '>', $currentLevel->rank_order)
                ->orderBy('rank_order')
                ->get();
        }

        // Check defined CareerPaths referencing this profile's job
        $paths = CareerPath::where('tenant_id', $tenantId)
            ->whereHas('steps', function ($q) use ($profile) {
                $q->where('job_id', $profile->job_id);
            })
            ->with(['steps.job'])
            ->get();

        return [
            'current_profile' => $profile,
            'career_track' => $track,
            'current_level' => $currentLevel,
            'possible_next_levels' => $nextLevels,
            'associated_career_paths' => $paths,
        ];
    }
}
