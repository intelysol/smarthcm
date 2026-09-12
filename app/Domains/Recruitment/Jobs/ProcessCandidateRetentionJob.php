<?php

namespace App\Domains\Recruitment\Jobs;

use App\Domains\Recruitment\Models\HcmRecruitmentCandidate;
use App\Domains\Recruitment\Models\HcmRecruitmentCandidateConsent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessCandidateRetentionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        // Find consents past retention expiry that are NOT under legal hold
        $expiredConsents = HcmRecruitmentCandidateConsent::where('is_legal_hold', false)
            ->whereNotNull('retention_expiry_date')
            ->where('retention_expiry_date', '<', now()->toDateString())
            ->get();

        foreach ($expiredConsents as $consent) {
            $candidate = $consent->candidate;
            if ($candidate && $candidate->status !== 'hired') {
                // Anonymize sensitive candidate profile info
                $candidate->update([
                    'first_name' => 'Anonymized',
                    'last_name' => 'Candidate',
                    'email' => 'anonymized_' . bin2hex(random_bytes(4)) . '@example.invalid',
                    'phone' => null,
                    'status' => 'archived',
                ]);

                if ($candidate->profile) {
                    $candidate->profile->update([
                        'headline' => null,
                        'summary' => null,
                        'resume_path' => null,
                        'linkedin_url' => null,
                        'github_url' => null,
                    ]);
                }
            }
        }
    }
}
