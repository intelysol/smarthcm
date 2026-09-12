<?php

namespace App\Domains\OrganizationDesign\Services;

use App\Domains\OrganizationDesign\Models\CareerLevel;
use App\Domains\OrganizationDesign\Models\CareerTrack;
use App\Domains\OrganizationDesign\Models\JobFamily;
use App\Domains\OrganizationDesign\Models\JobLevel;
use App\Domains\OrganizationDesign\Models\JobSubFamily;
use Illuminate\Support\Collection;

class JobArchitectureService
{
    /**
     * Get all job families with sub-families for a tenant.
     */
    public function getFamilies(string $tenantId): Collection
    {
        return JobFamily::where('tenant_id', $tenantId)
            ->with(['subFamilies'])
            ->orderBy('name')
            ->get();
    }

    /**
     * Create a new Job Family.
     */
    public function createFamily(string $tenantId, array $data): JobFamily
    {
        return JobFamily::create([
            'tenant_id' => $tenantId,
            'code' => strtoupper($data['code']),
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? 'active',
        ]);
    }

    /**
     * Create a Job Sub-Family under a Job Family.
     */
    public function createSubFamily(JobFamily $family, array $data): JobSubFamily
    {
        return JobSubFamily::create([
            'tenant_id' => $family->tenant_id,
            'job_family_id' => $family->id,
            'code' => strtoupper($data['code']),
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? 'active',
        ]);
    }

    /**
     * Create or retrieve Career Track.
     */
    public function createCareerTrack(string $tenantId, array $data): CareerTrack
    {
        return CareerTrack::create([
            'tenant_id' => $tenantId,
            'code' => strtoupper($data['code']),
            'name' => $data['name'],
            'track_type' => $data['track_type'] ?? 'individual_contributor',
            'description' => $data['description'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    /**
     * Add Career Level to a Career Track.
     */
    public function addCareerLevel(CareerTrack $track, array $data): CareerLevel
    {
        return CareerLevel::create([
            'tenant_id' => $track->tenant_id,
            'career_track_id' => $track->id,
            'level_code' => strtoupper($data['level_code']),
            'name' => $data['name'],
            'rank_order' => (int) ($data['rank_order'] ?? 1),
            'typical_experience_years' => (float) ($data['typical_experience_years'] ?? 0),
            'scope_description' => $data['scope_description'] ?? null,
        ]);
    }

    /**
     * Create Job Level.
     */
    public function createJobLevel(string $tenantId, array $data): JobLevel
    {
        return JobLevel::create([
            'tenant_id' => $tenantId,
            'code' => strtoupper($data['code']),
            'name' => $data['name'],
            'numerical_level' => (int) ($data['numerical_level'] ?? 1),
            'description' => $data['description'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    /**
     * Get complete Job Architecture tree for a tenant.
     */
    public function getArchitectureTree(string $tenantId): array
    {
        $families = JobFamily::where('tenant_id', $tenantId)
            ->with(['subFamilies.jobProfiles', 'jobProfiles'])
            ->get();

        $tracks = CareerTrack::where('tenant_id', $tenantId)
            ->with(['careerLevels'])
            ->get();

        $levels = JobLevel::where('tenant_id', $tenantId)
            ->orderBy('numerical_level')
            ->get();

        return [
            'families' => $families,
            'career_tracks' => $tracks,
            'job_levels' => $levels,
        ];
    }
}
