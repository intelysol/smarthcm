<?php

namespace App\Domains\Offboarding\Services;

use App\Domains\Offboarding\Enums\ClearanceStatus;
use App\Domains\Offboarding\Models\SeparationRequest;

class SeparationAiService
{
    public function summarizeOffboardingStatus(SeparationRequest $request): array
    {
        $employee = $request->employee;
        $type = $request->separationType->name;
        $lwd = $request->approved_last_working_day ?? $request->proposed_last_working_day;

        $blockedDepartments = $request->clearances()
            ->where('status', '!=', ClearanceStatus::CLEARED->value)
            ->pluck('department')
            ->toArray();

        $summary = sprintf(
            "Separation %s for %s (%s). Target last working day: %s. Current status: %s. Pending clearance departments: %s.",
            $request->request_number,
            $employee->fullName(),
            $type,
            $lwd ? $lwd->format('M d, Y') : 'Pending',
            ucwords(str_replace('_', ' ', $request->status)),
            !empty($blockedDepartments) ? implode(', ', $blockedDepartments) : 'All cleared'
        );

        return [
            'request_id' => $request->id,
            'summary' => $summary,
            'is_advisory' => true,
        ];
    }

    public function draftRelievingLetter(SeparationRequest $request): array
    {
        $employee = $request->employee;
        $lwd = $request->approved_last_working_day ?? $request->proposed_last_working_day;
        $formattedLwd = $lwd ? $lwd->format('F d, Y') : 'your final working day';

        $body = "Dear {$employee->first_name},\n\nThis letter formally confirms that you have been relieved of your duties at Flow HCM effective {$formattedLwd}.\n\nWe confirm that you have completed all organizational clearance protocols and handed over your responsibilities in good standing. We sincerely thank you for your dedicated service and wish you the greatest success in your future endeavors.\n\nSincerely,\nHuman Resources Department\nFlow HCM Enterprise Platform";

        return [
            'subject' => "Relieving & Experience Confirmation — {$employee->first_name} {$employee->last_name}",
            'body' => $body,
            'is_advisory' => true,
        ];
    }

    public function processAiInquiry(string $tenantId, string $query): array
    {
        $normalized = strtolower($query);

        if (str_contains($normalized, 'should i terminate')
            || str_contains($normalized, 'terminate this employee')
            || str_contains($normalized, 'fire')
            || str_contains($normalized, 'select for redundancy')
            || str_contains($normalized, 'determine severance')
        ) {
            return [
                'error' => 'Blocked by AI HCM Safety Guardrails: AI cannot recommend or decide employee termination, select redundancy candidates, or determine severance.',
                'status' => 'blocked_by_guardrails',
                'is_advisory' => true,
            ];
        }

        return [
            'query' => $query,
            'response' => 'Policy Guidance: Standard offboarding procedures require clearance sign-off across HR, Finance, and IT before final settlement and relieving letter generation.',
            'status' => 'success',
            'is_advisory' => true,
        ];
    }
}
