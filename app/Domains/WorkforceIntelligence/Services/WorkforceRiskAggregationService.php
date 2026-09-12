<?php

namespace App\Domains\WorkforceIntelligence\Services;

use App\Domains\WorkforceIntelligence\Models\CommandCenterRisk;
use Carbon\Carbon;
use Illuminate\Support\Str;

class WorkforceRiskAggregationService
{
    public function detectAndAggregateRisks(string $tenantId, ?string $departmentId = null): array
    {
        $risks = [];

        // Sample automatic cross-domain risk detection
        $capacityRiskCode = 'RSK-CAP-' . Str::upper(Str::random(6));
        $risk = CommandCenterRisk::firstOrCreate(
            [
                'tenant_id' => $tenantId,
                'category' => 'CAPACITY',
                'title' => 'Assembly Shift 2 Under-allocation',
            ],
            [
                'risk_code' => $capacityRiskCode,
                'department_id' => $departmentId,
                'severity' => 'HIGH',
                'impact_score' => 78.0,
                'likelihood_score' => 65.0,
                'exposure_value' => 45000.00,
                'description' => 'Shift 2 runs at 72% capacity requirement, resulting in continuous overtime leakages.',
                'status' => 'IDENTIFIED',
                'attribution_level' => 'OBSERVED',
                'causal_factors' => ['Unfilled operator vacancies', 'Higher seasonal order volume'],
                'recommended_mitigation' => 'Deploy cross-trained operators from Warehouse packing line.',
            ]
        );
        $risks[] = $risk;

        // Skill Risk
        $skillRisk = CommandCenterRisk::firstOrCreate(
            [
                'tenant_id' => $tenantId,
                'category' => 'SKILLS',
                'title' => 'Single Point of Failure (SPOF): Industrial Automation Lead',
            ],
            [
                'risk_code' => 'RSK-SKL-' . Str::upper(Str::random(6)),
                'department_id' => $departmentId,
                'severity' => 'CRITICAL',
                'impact_score' => 92.0,
                'likelihood_score' => 40.0,
                'exposure_value' => 120000.00,
                'description' => 'Only 1 certified PLC engineer available across the plant. Absence creates immediate line shutdown risk.',
                'status' => 'IDENTIFIED',
                'attribution_level' => 'CORRELATED',
                'causal_factors' => ['Specialized equipment commissioning', 'Lack of succession candidates'],
                'recommended_mitigation' => 'Initiate fast-track cross-training for two Senior Maintenance Technicians.',
            ]
        );
        $risks[] = $skillRisk;

        return $risks;
    }

    public function getRisks(string $tenantId, ?string $departmentId = null, ?string $category = null, ?string $severity = null): array
    {
        $q = CommandCenterRisk::where('tenant_id', $tenantId);
        if ($departmentId) {
            $q->where('department_id', $departmentId);
        }
        if ($category) {
            $q->where('category', $category);
        }
        if ($severity) {
            $q->where('severity', $severity);
        }

        return $q->orderBy('impact_score', 'desc')->get()->toArray();
    }
}
