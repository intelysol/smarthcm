<?php

namespace App\Domains\Recruitment\Services;

use App\Domains\Recruitment\Models\HcmRecruitmentCandidate;
use App\Domains\Recruitment\Models\HcmRecruitmentCandidateConsent;
use App\Domains\Recruitment\Models\HcmRecruitmentCandidateProfile;
use App\Domains\Recruitment\Models\HcmRecruitmentCandidateTag;
use App\Domains\Recruitment\Models\HcmRecruitmentTalentPool;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class CandidateService
{
    public function createCandidate(array $data): HcmRecruitmentCandidate
    {
        return DB::transaction(function () use ($data) {
            $candidateNumber = 'CAN-' . strtoupper(bin2hex(random_bytes(4)));

            $candidate = HcmRecruitmentCandidate::create([
                'tenant_id' => $data['tenant_id'],
                'candidate_number' => $candidateNumber,
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => strtolower(trim($data['email'])),
                'phone' => $data['phone'] ?? null,
                'location' => $data['location'] ?? null,
                'country' => $data['country'] ?? null,
                'source_id' => $data['source_id'] ?? null,
                'consent_status' => 'given',
                'consent_at' => now(),
                'status' => 'active',
            ]);

            // Create profile
            HcmRecruitmentCandidateProfile::create([
                'tenant_id' => $candidate->tenant_id,
                'candidate_id' => $candidate->id,
                'headline' => $data['headline'] ?? null,
                'summary' => $data['summary'] ?? null,
                'resume_path' => $data['resume_path'] ?? null,
                'resume_filename' => $data['resume_filename'] ?? null,
                'expected_salary' => $data['expected_salary'] ?? null,
                'currency' => $data['currency'] ?? 'USD',
                'skills' => $data['skills'] ?? [],
                'experience_history' => $data['experience_history'] ?? [],
                'education_history' => $data['education_history'] ?? [],
            ]);

            // Record consent
            HcmRecruitmentCandidateConsent::create([
                'tenant_id' => $candidate->tenant_id,
                'candidate_id' => $candidate->id,
                'consent_type' => 'recruitment_processing',
                'version' => 'v1.0',
                'is_granted' => true,
                'granted_at' => now(),
                'retention_expiry_date' => now()->addYears(2)->toDateString(),
            ]);

            // Attach tags if provided
            if (!empty($data['tags'])) {
                foreach ($data['tags'] as $tag) {
                    $this->addTag($candidate, $tag);
                }
            }

            return $candidate;
        });
    }

    public function addTag(HcmRecruitmentCandidate $candidate, string $tag): HcmRecruitmentCandidateTag
    {
        return HcmRecruitmentCandidateTag::firstOrCreate([
            'tenant_id' => $candidate->tenant_id,
            'candidate_id' => $candidate->id,
            'tag' => trim($tag),
        ]);
    }

    public function detectDuplicates(string $tenantId, string $email, ?string $phone = null): Collection
    {
        $query = HcmRecruitmentCandidate::where('tenant_id', $tenantId)
            ->where(function ($q) use ($email, $phone) {
                $q->where('email', strtolower(trim($email)));
                if (!empty($phone)) {
                    $cleanedPhone = preg_replace('/[^0-9]/', '', $phone);
                    if (strlen($cleanedPhone) >= 7) {
                        $q->orWhere('phone', 'like', "%{$cleanedPhone}%");
                    }
                }
            });

        return $query->get();
    }

    public function addToTalentPool(HcmRecruitmentCandidate $candidate, HcmRecruitmentTalentPool $pool): void
    {
        $pool->candidates()->syncWithoutDetaching([$candidate->id => ['tenant_id' => $candidate->tenant_id]]);
    }
}
