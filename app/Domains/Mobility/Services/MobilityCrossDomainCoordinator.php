<?php

namespace App\Domains\Mobility\Services;

use App\Domains\Mobility\Models\MobilityAssignment;
use App\Domains\Mobility\Models\MobilityBenefitLink;
use App\Domains\Mobility\Models\MobilityCompensationLink;
use App\Domains\Mobility\Models\MobilityComplianceLink;
use App\Domains\Mobility\Models\MobilityDocumentLink;
use App\Domains\Mobility\Models\MobilityExpenseLink;

class MobilityCrossDomainCoordinator
{
    /**
     * Link an existing Visa / Work Permit record from Compliance domain (Epic 2.33).
     */
    public function linkComplianceRecord(
        MobilityAssignment $assignment,
        string $complianceType,
        string $recordId,
        ?string $validUntil = null
    ): MobilityComplianceLink {
        return MobilityComplianceLink::create([
            'tenant_id' => $assignment->tenant_id,
            'assignment_id' => $assignment->id,
            'compliance_type' => $complianceType,
            'compliance_record_id' => $recordId,
            'compliance_status' => 'approved',
            'valid_until' => $validUntil,
        ]);
    }

    /**
     * Link an Employee Document (Epic 2.30).
     */
    public function linkDocument(
        MobilityAssignment $assignment,
        string $documentType,
        string $documentId
    ): MobilityDocumentLink {
        return MobilityDocumentLink::create([
            'tenant_id' => $assignment->tenant_id,
            'assignment_id' => $assignment->id,
            'document_type' => $documentType,
            'document_id' => $documentId,
            'verification_status' => 'verified',
        ]);
    }

    /**
     * Link an Expense Claim or Travel Request (Epic 2.38).
     */
    public function linkExpenseEntity(
        MobilityAssignment $assignment,
        string $linkType,
        string $entityId,
        float $amount,
        string $currency = 'USD'
    ): MobilityExpenseLink {
        return MobilityExpenseLink::create([
            'tenant_id' => $assignment->tenant_id,
            'assignment_id' => $assignment->id,
            'link_type' => $linkType,
            'entity_id' => $entityId,
            'amount' => $amount,
            'currency' => $currency,
        ]);
    }

    /**
     * Link Benefit Plan / Election (Epic 2.37).
     */
    public function linkBenefitPlan(
        MobilityAssignment $assignment,
        string $planId,
        ?string $electionId = null,
        string $category = 'international_health'
    ): MobilityBenefitLink {
        return MobilityBenefitLink::create([
            'tenant_id' => $assignment->tenant_id,
            'assignment_id' => $assignment->id,
            'benefit_plan_id' => $planId,
            'benefit_election_id' => $electionId,
            'benefit_category' => $category,
            'status' => 'active',
        ]);
    }

    /**
     * Link Compensation Recommendation / Allowances (Epic 2.36).
     */
    public function linkCompensationPackage(
        MobilityAssignment $assignment,
        array $compData
    ): MobilityCompensationLink {
        return MobilityCompensationLink::create([
            'tenant_id' => $assignment->tenant_id,
            'assignment_id' => $assignment->id,
            'compensation_recommendation_id' => $compData['compensation_recommendation_id'] ?? null,
            'home_salary' => $compData['home_salary'] ?? null,
            'host_salary' => $compData['host_salary'] ?? null,
            'mobility_allowance' => $compData['mobility_allowance'] ?? 0.0,
            'cost_of_living_allowance' => $compData['cost_of_living_allowance'] ?? 0.0,
            'housing_allowance' => $compData['housing_allowance'] ?? 0.0,
            'hardship_allowance' => $compData['hardship_allowance'] ?? 0.0,
            'currency' => $compData['currency'] ?? $assignment->assignment_currency ?? 'USD',
        ]);
    }
}
