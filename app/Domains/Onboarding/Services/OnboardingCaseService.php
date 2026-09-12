<?php

namespace App\Domains\Onboarding\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\Onboarding\Enums\OnboardingCaseStatus;
use App\Domains\Onboarding\Enums\OnboardingTaskStatus;
use App\Domains\Onboarding\Enums\ProbationStatus;
use App\Domains\Onboarding\Models\HcmOnboardingCase;
use App\Domains\Onboarding\Models\HcmOnboardingCaseTask;
use App\Domains\Onboarding\Models\HcmOnboardingDocumentRequirement;
use App\Domains\Onboarding\Models\HcmOnboardingProbation;
use App\Domains\Onboarding\Models\HcmOnboardingTemplate;
use App\Domains\Onboarding\Models\HcmOnboardingTemplateVersion;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OnboardingCaseService
{
    public function initializeCaseForEmployee(Employee $employee, array $options = []): HcmOnboardingCase
    {
        // Prevent duplicate active onboarding case for the same employee
        $existing = HcmOnboardingCase::where('employee_id', $employee->id)
            ->whereNotIn('status', [OnboardingCaseStatus::COMPLETED->value, OnboardingCaseStatus::CANCELLED->value])
            ->first();

        if ($existing) {
            throw ValidationException::withMessages(['employee' => 'An active onboarding case already exists for this employee.']);
        }

        return DB::transaction(function () use ($employee, $options) {
            // 1. Resolve template version
            $templateVersion = null;
            if (!empty($options['template_version_id'])) {
                $templateVersion = HcmOnboardingTemplateVersion::findOrFail($options['template_version_id']);
            } else {
                $template = $this->resolveTemplateByRules($employee);
                $templateVersion = $template->latestVersion();
            }

            if (!$templateVersion) {
                throw ValidationException::withMessages(['template' => 'No published onboarding template version available for this employee profile.']);
            }

            $startDate = !empty($options['start_date']) ? Carbon::parse($options['start_date']) : ($employee->joining_date ?? now()->addDays(7));
            $caseNumber = 'ONB-' . strtoupper(bin2hex(random_bytes(4)));

            $case = HcmOnboardingCase::create([
                'tenant_id' => $employee->tenant_id,
                'case_number' => $caseNumber,
                'employee_id' => $employee->id,
                'recruitment_application_id' => $options['recruitment_application_id'] ?? null,
                'offer_id' => $options['offer_id'] ?? null,
                'template_version_id' => $templateVersion->id,
                'start_date' => $startDate->toDateString(),
                'target_completion_date' => $startDate->copy()->addDays(30)->toDateString(),
                'status' => OnboardingCaseStatus::PREBOARDING->value,
                'completion_percentage' => 0.00,
                'owner_id' => $options['owner_id'] ?? null,
            ]);

            // 2. Generate Concrete Tasks from Template Version Snapshot
            $templateTasks = $templateVersion->tasks;
            $createdTasks = [];
            foreach ($templateTasks as $templateTask) {
                $dueDate = $startDate->copy()->addDays($templateTask->due_offset_days);

                $caseTask = HcmOnboardingCaseTask::create([
                    'tenant_id' => $case->tenant_id,
                    'case_id' => $case->id,
                    'template_task_id' => $templateTask->id,
                    'title' => $templateTask->title,
                    'description' => $templateTask->description,
                    'task_type' => $templateTask->task_type,
                    'owner_role' => $templateTask->owner_role,
                    'assigned_to_employee_id' => $templateTask->owner_role === 'employee' ? $employee->id : null,
                    'due_date' => $dueDate->toDateString(),
                    'status' => OnboardingTaskStatus::PENDING->value,
                    'is_required' => $templateTask->is_required,
                ]);

                $createdTasks[$templateTask->id] = $caseTask;
            }

            // 3. Initialize Standard Document Requirements
            $standardDocs = [
                ['type' => 'id_proof', 'title' => 'National ID / Passport Verification'],
                ['type' => 'address_proof', 'title' => 'Proof of Address / Utility Bill'],
                ['type' => 'education_cert', 'title' => 'Highest Degree / Diploma Certificate'],
                ['type' => 'signed_offer', 'title' => 'Countersigned Employment Agreement'],
            ];

            foreach ($standardDocs as $doc) {
                HcmOnboardingDocumentRequirement::create([
                    'tenant_id' => $case->tenant_id,
                    'case_id' => $case->id,
                    'document_type' => $doc['type'],
                    'title' => $doc['title'],
                    'is_mandatory' => true,
                    'status' => 'pending',
                ]);
            }

            // 4. Initialize Probation Tracking (e.g. 90 days)
            HcmOnboardingProbation::create([
                'tenant_id' => $case->tenant_id,
                'case_id' => $case->id,
                'employee_id' => $employee->id,
                'probation_start_date' => $startDate->toDateString(),
                'probation_end_date' => $startDate->copy()->addDays(90)->toDateString(),
                'status' => ProbationStatus::IN_PROGRESS->value,
            ]);

            return $case;
        });
    }

    public function resolveTemplateByRules(Employee $employee): HcmOnboardingTemplate
    {
        $tenantId = $employee->tenant_id;

        // Try department-specific template
        if ($employee->department_id) {
            $deptTemplate = HcmOnboardingTemplate::where('tenant_id', $tenantId)
                ->where('department_id', $employee->department_id)
                ->where('is_active', true)
                ->first();

            if ($deptTemplate) {
                return $deptTemplate;
            }
        }

        // Fallback to default active template
        $default = HcmOnboardingTemplate::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->first();

        if ($default) {
            return $default;
        }

        // Create fallback if none exists
        $created = HcmOnboardingTemplate::create([
            'tenant_id' => $tenantId,
            'name' => 'Corporate New Hire Template',
            'code' => 'TMPL-CORP-DEFAULT',
            'description' => 'Default corporate onboarding checklist.',
            'is_active' => true,
        ]);

        $version = HcmOnboardingTemplateVersion::create([
            'tenant_id' => $tenantId,
            'template_id' => $created->id,
            'version_number' => 1,
            'status' => 'published',
        ]);

        return $created;
    }

    public function recalculateProgress(HcmOnboardingCase $case): HcmOnboardingCase
    {
        $totalRequired = $case->tasks()->where('is_required', true)->count();
        $completedRequired = $case->tasks()->where('is_required', true)->where('status', OnboardingTaskStatus::COMPLETED->value)->count();

        $percentage = $totalRequired > 0 ? round(($completedRequired / $totalRequired) * 100, 2) : 100.00;

        $updates = ['completion_percentage' => $percentage];

        if ($percentage >= 100.00 && $case->status !== OnboardingCaseStatus::COMPLETED->value) {
            $updates['status'] = OnboardingCaseStatus::COMPLETED->value;
            $updates['completed_at'] = now();
        } elseif ($percentage > 0 && $case->status === OnboardingCaseStatus::PREBOARDING->value && now()->greaterThanOrEqualTo($case->start_date)) {
            $updates['status'] = OnboardingCaseStatus::IN_PROGRESS->value;
        }

        $case->update($updates);
        return $case;
    }
}
