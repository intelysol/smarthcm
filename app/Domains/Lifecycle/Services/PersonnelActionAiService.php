<?php

namespace App\Domains\Lifecycle\Services;

use App\Domains\Lifecycle\Models\PersonnelActionRequest;

class PersonnelActionAiService
{
    public function summarizeAction(PersonnelActionRequest $request): array
    {
        $employee = $request->employee;
        $actionName = $request->actionType->name;
        $effective = $request->effective_date->format('M d, Y');

        $changeDescriptions = [];
        foreach ($request->changes as $change) {
            $changeDescriptions[] = "{$change->field_name} changed from '{$change->old_value_label}' to '{$change->new_value_label}'";
        }

        $summaryText = sprintf(
            "Personnel action %s for %s (%s) scheduled for effective execution on %s. Key adjustments: %s.",
            $request->request_number,
            $employee->fullName(),
            $actionName,
            $effective,
            implode('; ', $changeDescriptions)
        );

        return [
            'request_id' => $request->id,
            'summary' => $summaryText,
            'is_advisory' => true,
        ];
    }

    public function draftPromotionLetter(PersonnelActionRequest $request): array
    {
        $employee = $request->employee;
        $effective = $request->effective_date->format('F d, Y');

        $body = "Dear {$employee->first_name},\n\nWe are delighted to congratulate you on your official promotion at Flow HCM, effective {$effective}. Your dedication, domain leadership, and team achievements have made a profound impact.\n\nWe look forward to your continued success in this expanded role.\n\nSincerely,\nHuman Resources & Leadership Team";

        return [
            'subject' => "Congratulations on Your Promotion, {$employee->first_name}!",
            'body' => $body,
            'is_advisory' => true,
        ];
    }

    public function processAiInquiry(string $tenantId, string $query): array
    {
        $normalized = strtolower($query);

        if (str_contains($normalized, 'promote automatically') || str_contains($normalized, 'increase salary by') || str_contains($normalized, 'terminate') || str_contains($normalized, 'fire')) {
            return [
                'error' => 'Blocked by AI HCM Safety Guardrails: AI cannot make autonomous promotion, compensation, or termination decisions.',
                'status' => 'blocked_by_guardrails',
                'is_advisory' => true,
            ];
        }

        return [
            'query' => $query,
            'response' => 'Policy Guidance: Standard promotion workflows require department head endorsement, HR review, and executive budget authorization.',
            'status' => 'success',
            'is_advisory' => true,
        ];
    }
}
