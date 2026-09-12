<?php

namespace App\Domains\Mobility\Services;

use App\Domains\Mobility\Models\MobilityAssignment;
use App\Domains\Mobility\Models\MobilityRequest;

class MobilityAiAdvisoryService
{
    /**
     * Generate an advisory briefing for an assignment or request.
     * Advisory only: does NOT make autonomous decisions or mutate state.
     */
    public function generateBriefing(MobilityAssignment $assignment): array
    {
        $insights = [];
        $alerts = [];
        $recommendations = [];

        // 1. Relocation check
        if (!$assignment->relocationCase()->exists()) {
            $alerts[] = 'No relocation case opened for this cross-border movement.';
            $recommendations[] = 'Initiate standard relocation case for household goods and temporary accommodation.';
        }

        // 2. Compliance check
        $hasActiveCompliance = $assignment->complianceLinks()->where('compliance_status', 'approved')->exists();
        if (!$hasActiveCompliance) {
            $alerts[] = 'Host country visa or work permit verification is pending.';
            $recommendations[] = 'Verify immigration documents in the Compliance domain prior to departure.';
        }

        // 3. Cost Allocation check
        $hasAllocations = $assignment->costAllocations()->exists();
        if (!$hasAllocations) {
            $alerts[] = 'Intercompany cost allocation has not been configured.';
            $recommendations[] = 'Define Home/Host percentage split to enable Finance GL integration.';
        }

        $insights[] = "Assignment from {$assignment->home_country} to {$assignment->host_country} is scheduled for {$assignment->start_date} to {$assignment->planned_end_date}.";

        return [
            'is_advisory_only' => true,
            'summary' => "Mobility Advisory Briefing for Assignment {$assignment->assignment_number}",
            'insights' => $insights,
            'alerts' => $alerts,
            'recommendations' => $recommendations,
            'generated_at' => now()->toIso8601String(),
        ];
    }
}
