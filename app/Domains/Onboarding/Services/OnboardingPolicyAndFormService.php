<?php

namespace App\Domains\Onboarding\Services;

use App\Domains\Onboarding\Models\HcmOnboardingCase;
use App\Domains\Onboarding\Models\HcmOnboardingForm;
use App\Domains\Onboarding\Models\HcmOnboardingFormSubmission;
use App\Domains\Onboarding\Models\HcmOnboardingPolicyAcknowledgement;

class OnboardingPolicyAndFormService
{
    public function acknowledgePolicy(HcmOnboardingCase $case, string $policyCode, string $policyTitle, string $version = 'v1.0', ?string $ipAddress = null): HcmOnboardingPolicyAcknowledgement
    {
        return HcmOnboardingPolicyAcknowledgement::updateOrCreate(
            [
                'case_id' => $case->id,
                'policy_code' => $policyCode,
                'policy_version' => $version,
            ],
            [
                'tenant_id' => $case->tenant_id,
                'employee_id' => $case->employee_id,
                'policy_title' => $policyTitle,
                'acknowledged_at' => now(),
                'ip_address' => $ipAddress,
            ]
        );
    }

    public function submitDigitalForm(HcmOnboardingCase $case, HcmOnboardingForm $form, array $formData): HcmOnboardingFormSubmission
    {
        return HcmOnboardingFormSubmission::updateOrCreate(
            [
                'case_id' => $case->id,
                'form_id' => $form->id,
            ],
            [
                'tenant_id' => $case->tenant_id,
                'form_data' => $formData,
                'submitted_at' => now(),
            ]
        );
    }
}
