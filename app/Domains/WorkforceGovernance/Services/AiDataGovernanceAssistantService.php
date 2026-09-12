<?php

namespace App\Domains\WorkforceGovernance\Services;

use App\Domains\WorkforceGovernance\Models\HcmGovQualityIssue;
use App\Domains\WorkforceGovernance\Models\HcmGovReconciliation;

class AiDataGovernanceAssistantService
{
    public function explainIssue(string $issueId): array
    {
        $issue = HcmGovQualityIssue::with('rule')->findOrFail($issueId);

        $rootCauseHypothesis = match ($issue->rule->dimension) {
            'COMPLETENESS' => 'A mandatory personnel field was omitted during initial employee self-service onboarding or bulk ERP integration.',
            'VALIDITY' => 'An inverted date or invalid code value bypassed frontend form validation due to historical data migration.',
            'CONSISTENCY' => 'A department reassignment was processed in Core HR but not yet synchronized with the Payroll general ledger.',
            default => 'Standard data entry discrepancy requiring authoritative steward review.',
        };

        $suggestedAction = match ($issue->rule->dimension) {
            'COMPLETENESS' => 'Contact the hiring manager or employee to supply the missing ' . ($issue->target_field ?? 'information') . '.',
            'VALIDITY' => 'Review the original employment contract to establish the verified effective dates.',
            'CONSISTENCY' => 'Trigger the master data mapping reconciliation job to sync the authoritative Core HR record to Payroll.',
            default => 'Perform manual validation against authoritative source documentation.',
        };

        return [
            'issue_code' => $issue->issue_code,
            'severity' => $issue->severity,
            'rule_violated' => $issue->rule->name,
            'ai_analysis' => [
                'root_cause_hypothesis' => $rootCauseHypothesis,
                'suggested_remediation' => $suggestedAction,
                'safety_notice' => 'AI suggestions are advisory. Do not modify authoritative records without verified authorization.',
            ],
        ];
    }

    public function explainReconciliationDiscrepancy(string $reconciliationId): array
    {
        $rec = HcmGovReconciliation::findOrFail($reconciliationId);

        return [
            'reconciliation_code' => $rec->reconciliation_code,
            'source_domain' => $rec->source_domain,
            'target_domain' => $rec->target_domain,
            'summary' => "Reconciliation identified {$rec->unmatched_count} discrepancies between {$rec->source_domain} ({$rec->source_record_count} records) and {$rec->target_domain} ({$rec->target_record_count} records).",
            'discrepancy_breakdown' => $rec->discrepancy_details,
            'recommended_next_step' => 'Review unmatched employee identifiers and verify active employment statuses across both systems before executing payroll cycle.',
        ];
    }
}
