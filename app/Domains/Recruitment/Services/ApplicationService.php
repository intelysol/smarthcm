<?php

namespace App\Domains\Recruitment\Services;

use App\Domains\Recruitment\Enums\ApplicationStatus;
use App\Domains\Recruitment\Models\HcmRecruitmentApplication;
use App\Domains\Recruitment\Models\HcmRecruitmentApplicationActivity;
use App\Domains\Recruitment\Models\HcmRecruitmentApplicationStage;
use App\Domains\Recruitment\Models\HcmRecruitmentCandidate;
use App\Domains\Recruitment\Models\HcmRecruitmentRequisition;
use App\Domains\Recruitment\Models\HcmRecruitmentScreening;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ApplicationService
{
    public function apply(HcmRecruitmentCandidate $candidate, HcmRecruitmentRequisition $requisition, array $data): HcmRecruitmentApplication
    {
        // Prevent duplicate active application for same requisition
        $existing = HcmRecruitmentApplication::where('candidate_id', $candidate->id)
            ->where('requisition_id', $requisition->id)
            ->first();

        if ($existing) {
            throw ValidationException::withMessages(['application' => 'Candidate has already applied for this job requisition.']);
        }

        return DB::transaction(function () use ($candidate, $requisition, $data) {
            $firstStage = HcmRecruitmentApplicationStage::where('tenant_id', $requisition->tenant_id)
                ->orderBy('stage_order')
                ->first();

            $appNumber = 'APP-' . strtoupper(bin2hex(random_bytes(4)));

            $application = HcmRecruitmentApplication::create([
                'tenant_id' => $requisition->tenant_id,
                'application_number' => $appNumber,
                'candidate_id' => $candidate->id,
                'requisition_id' => $requisition->id,
                'stage_id' => $firstStage?->id,
                'status' => ApplicationStatus::NEW->value,
                'source_id' => $data['source_id'] ?? $candidate->source_id,
                'cover_letter' => $data['cover_letter'] ?? null,
                'applied_at' => now(),
            ]);

            HcmRecruitmentApplicationActivity::create([
                'tenant_id' => $application->tenant_id,
                'application_id' => $application->id,
                'actor_id' => $data['actor_id'] ?? null,
                'activity_type' => 'application_created',
                'from_state' => null,
                'to_state' => ApplicationStatus::NEW->value,
                'notes' => 'Application submitted successfully.',
            ]);

            return $application;
        });
    }

    public function transitionStage(HcmRecruitmentApplication $application, HcmRecruitmentApplicationStage $newStage, ?int $actorId = null, ?string $notes = null): HcmRecruitmentApplication
    {
        $oldStageName = $application->stage?->name ?? 'Initial';

        $application->update([
            'stage_id' => $newStage->id,
        ]);

        HcmRecruitmentApplicationActivity::create([
            'tenant_id' => $application->tenant_id,
            'application_id' => $application->id,
            'actor_id' => $actorId,
            'activity_type' => 'stage_change',
            'from_state' => $oldStageName,
            'to_state' => $newStage->name,
            'notes' => $notes,
        ]);

        return $application;
    }

    public function screenApplication(HcmRecruitmentApplication $application, array $criteria, ?int $screenedBy = null): HcmRecruitmentScreening
    {
        return DB::transaction(function () use ($application, $criteria, $screenedBy) {
            $screening = HcmRecruitmentScreening::create([
                'tenant_id' => $application->tenant_id,
                'application_id' => $application->id,
                'screened_by' => $screenedBy,
                'skills_match' => $criteria['skills_match'] ?? false,
                'experience_match' => $criteria['experience_match'] ?? false,
                'education_match' => $criteria['education_match'] ?? false,
                'salary_match' => $criteria['salary_match'] ?? true,
                'result' => $criteria['result'] ?? 'passed',
                'feedback' => $criteria['feedback'] ?? null,
            ]);

            $newStatus = $screening->result === 'passed' ? ApplicationStatus::SHORTLISTED->value : ApplicationStatus::REJECTED->value;
            $application->update([
                'status' => $newStatus,
                'screening_score' => $criteria['score'] ?? null,
                'screening_notes' => $criteria['feedback'] ?? null,
                'rejected_at' => $newStatus === ApplicationStatus::REJECTED->value ? now() : null,
                'rejection_reason' => $newStatus === ApplicationStatus::REJECTED->value ? ($criteria['feedback'] ?? 'Screening criteria not met') : null,
            ]);

            HcmRecruitmentApplicationActivity::create([
                'tenant_id' => $application->tenant_id,
                'application_id' => $application->id,
                'actor_id' => $screenedBy,
                'activity_type' => 'screening_completed',
                'from_state' => ApplicationStatus::SCREENING->value,
                'to_state' => $newStatus,
                'notes' => "Screening result: {$screening->result}. " . ($criteria['feedback'] ?? ''),
            ]);

            return $screening;
        });
    }

    public function reject(HcmRecruitmentApplication $application, string $reason, ?int $actorId = null): HcmRecruitmentApplication
    {
        $application->update([
            'status' => ApplicationStatus::REJECTED->value,
            'rejected_at' => now(),
            'rejection_reason' => $reason,
        ]);

        HcmRecruitmentApplicationActivity::create([
            'tenant_id' => $application->tenant_id,
            'application_id' => $application->id,
            'actor_id' => $actorId,
            'activity_type' => 'status_change',
            'from_state' => $application->status,
            'to_state' => ApplicationStatus::REJECTED->value,
            'notes' => "Application rejected. Reason: {$reason}",
        ]);

        return $application;
    }
}
