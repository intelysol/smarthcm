<?php

namespace App\Domains\OrganizationDesign\Services;

use App\Domains\Organization\Models\Position;
use App\Domains\OrganizationDesign\Models\JobProfile;
use App\Domains\OrganizationDesign\Models\OrgDesignHealthIssue;

class ArchitectureHealthAndGovernanceService
{
    /**
     * Scan architecture health across job profiles, positions, and titles.
     */
    public function scanHealth(string $tenantId): array
    {
        $issues = [];

        // 1. Check for Profiles missing Family or Level
        $profilesMissingFamily = JobProfile::where('tenant_id', $tenantId)
            ->whereNull('job_family_id')
            ->get();

        foreach ($profilesMissingFamily as $profile) {
            $issues[] = OrgDesignHealthIssue::create([
                'tenant_id' => $tenantId,
                'issue_type' => 'missing_family',
                'severity' => 'warning',
                'entity_type' => 'job_profile',
                'entity_id' => $profile->id,
                'title' => "Job Profile '{$profile->title}' has no assigned Job Family.",
                'details' => ['code' => $profile->code],
                'status' => 'open',
            ]);
        }

        // 2. Check for Duplicate Title Variations (e.g. "Senior Software Engineer" vs "Sr Software Engineer")
        $allProfiles = JobProfile::where('tenant_id', $tenantId)->get();
        $normalizedTitles = [];

        foreach ($allProfiles as $profile) {
            $norm = strtolower(trim(preg_replace('/[^a-zA-Z0-9]/', '', str_ireplace(['sr.', 'sr', 'snr'], 'senior', $profile->title))));
            if (isset($normalizedTitles[$norm])) {
                $prior = $normalizedTitles[$norm];
                if ($prior->id !== $profile->id) {
                    $issues[] = OrgDesignHealthIssue::create([
                        'tenant_id' => $tenantId,
                        'issue_type' => 'duplicate_title',
                        'severity' => 'info',
                        'entity_type' => 'job_profile',
                        'entity_id' => $profile->id,
                        'title' => "Potential duplicate title variation between '{$profile->title}' and '{$prior->title}'.",
                        'details' => ['profile_a' => $prior->code, 'profile_b' => $profile->code],
                        'status' => 'open',
                    ]);
                }
            } else {
                $normalizedTitles[$norm] = $profile;
            }
        }

        // 3. Check for Positions referencing Retired Job Profiles
        $retiredJobIds = JobProfile::where('tenant_id', $tenantId)
            ->where('status', 'retired')
            ->pluck('job_id')
            ->filter()
            ->all();

        if (!empty($retiredJobIds)) {
            $positionsOnRetired = Position::where('tenant_id', $tenantId)
                ->whereIn('job_id', $retiredJobIds)
                ->get();

            foreach ($positionsOnRetired as $pos) {
                $issues[] = OrgDesignHealthIssue::create([
                    'tenant_id' => $tenantId,
                    'issue_type' => 'retired_reference',
                    'severity' => 'critical',
                    'entity_type' => 'position',
                    'entity_id' => $pos->id,
                    'title' => "Position '{$pos->title}' references a retired Job definition.",
                    'details' => ['position_code' => $pos->position_code ?? $pos->code],
                    'status' => 'open',
                ]);
            }
        }

        return [
            'total_issues_found' => count($issues),
            'issues' => $issues,
        ];
    }
}
