<?php

namespace App\Domains\Onboarding\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\Onboarding\Models\HcmOnboardingBuddyAssignment;
use App\Domains\Onboarding\Models\HcmOnboardingCase;
use App\Domains\Onboarding\Models\HcmOnboardingProvisioningRequest;

class OnboardingProvisioningService
{
    public function requestProvisioning(HcmOnboardingCase $case, string $type, string $title, array $specs = []): HcmOnboardingProvisioningRequest
    {
        return HcmOnboardingProvisioningRequest::create([
            'tenant_id' => $case->tenant_id,
            'case_id' => $case->id,
            'request_type' => $type,
            'title' => $title,
            'specifications' => $specs,
            'status' => 'pending',
        ]);
    }

    public function assignBuddy(HcmOnboardingCase $case, Employee $buddy, ?string $endDate = null): HcmOnboardingBuddyAssignment
    {
        return HcmOnboardingBuddyAssignment::updateOrCreate(
            ['case_id' => $case->id],
            [
                'tenant_id' => $case->tenant_id,
                'employee_id' => $case->employee_id,
                'buddy_employee_id' => $buddy->id,
                'start_date' => $case->start_date,
                'end_date' => $endDate,
                'status' => 'active',
            ]
        );
    }
}
