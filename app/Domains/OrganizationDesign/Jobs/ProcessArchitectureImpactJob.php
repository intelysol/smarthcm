<?php

namespace App\Domains\OrganizationDesign\Jobs;

use App\Domains\OrganizationDesign\Models\JobProfile;
use App\Domains\OrganizationDesign\Services\ArchitectureImpactAnalysisService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessArchitectureImpactJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public JobProfile $profile
    ) {}

    public function handle(ArchitectureImpactAnalysisService $impactService): void
    {
        $assessment = $impactService->assessJobProfileImpact($this->profile);
        Log::info("Computed architecture impact for job profile {$this->profile->code}", [
            'tenant_id' => $this->profile->tenant_id,
            'affected_positions' => $assessment['affected_positions_count'],
            'affected_employees' => $assessment['affected_employees_count'],
            'affected_requisitions' => $assessment['affected_requisitions_count'],
        ]);
    }
}
