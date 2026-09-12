<?php

namespace App\Domains\WorkforceProductivity\Services;

use App\Domains\WorkforceProductivity\Contracts\WorkforceRoiEvaluatorInterface;
use App\Domains\WorkforceProductivity\DTOs\WorkforceRoiData;
use App\Domains\WorkforceProductivity\Models\HcmProductivityRoiCalculation;
use App\Domains\WorkforceProductivity\Models\HcmProductivityRoiModel;
use Illuminate\Support\Str;

class WorkforceROIService implements WorkforceRoiEvaluatorInterface
{
    /**
     * Evaluate workforce investment ROI: (Net Benefit - Investment Cost) / Cost.
     */
    public function evaluateRoi(
        string $investmentName,
        string $investmentType,
        float $investmentCost,
        float $operationalBenefit,
        string $causality = 'CORRELATION',
        ?float $preRate = null,
        ?float $postRate = null,
        array $details = []
    ): WorkforceRoiData {
        $netBenefit = round($operationalBenefit - $investmentCost, 4);

        $roiPercentage = $investmentCost > 0
            ? round(($netBenefit / $investmentCost) * 100.0, 2)
            : 0.0;

        $paybackMonths = null;
        if ($netBenefit > 0 && $operationalBenefit > 0) {
            $monthlyBenefit = $operationalBenefit / 12.0;
            if ($monthlyBenefit > 0) {
                $paybackMonths = round($investmentCost / $monthlyBenefit, 2);
            }
        }

        return new WorkforceRoiData(
            investmentName: $investmentName,
            investmentType: $investmentType,
            investmentCost: round($investmentCost, 4),
            operationalBenefit: round($operationalBenefit, 4),
            netBenefit: $netBenefit,
            roiPercentage: $roiPercentage,
            paybackPeriodMonths: $paybackMonths,
            causalityLabel: in_array(strtoupper($causality), ['CAUSAL', 'EXPERIMENTAL'], true) ? 'CAUSAL' : 'CORRELATION',
            preInvestmentRate: $preRate,
            postInvestmentRate: $postRate,
            details: $details
        );
    }

    /**
     * Compute and persist Learning & Development Training ROI.
     */
    public function calculateTrainingRoi(
        string $tenantId,
        string $trainingProgramName,
        float $directTrainingCost,
        float $trainingHours,
        float $hourlyWageRate,
        float $preTrainingHourlyOutput,
        float $postTrainingHourlyOutput,
        float $unitValue,
        int $evaluationPeriodHours = 500,
        ?string $departmentId = null
    ): HcmProductivityRoiCalculation {
        // Total cost = direct cost + wage cost of training hours
        $opportunityWageCost = round($trainingHours * $hourlyWageRate, 4);
        $totalInvestmentCost = round($directTrainingCost + $opportunityWageCost, 4);

        // Operational benefit = (postOutput - preOutput) * evaluationPeriodHours * unitValue
        $rateDelta = max(0.0, $postTrainingHourlyOutput - $preTrainingHourlyOutput);
        $additionalOutput = round($rateDelta * $evaluationPeriodHours, 4);
        $grossOperationalBenefit = round($additionalOutput * $unitValue, 4);

        $roiData = $this->evaluateRoi(
            investmentName: "L&D: {$trainingProgramName}",
            investmentType: 'training',
            investmentCost: $totalInvestmentCost,
            operationalBenefit: $grossOperationalBenefit,
            causality: 'CORRELATION', // L&D is correlation unless experimental control group exists
            preRate: $preTrainingHourlyOutput,
            postRate: $postTrainingHourlyOutput,
            details: [
                'direct_cost' => $directTrainingCost,
                'wage_opportunity_cost' => $opportunityWageCost,
                'additional_units' => $additionalOutput,
                'unit_value' => $unitValue,
                'evaluation_period_hours' => $evaluationPeriodHours,
            ]
        );

        $model = HcmProductivityRoiModel::firstOrCreate(
            ['tenant_id' => $tenantId, 'investment_type' => 'training'],
            [
                'id' => Str::uuid()->toString(),
                'model_name' => 'Default Training ROI Model',
                'currency' => 'USD',
                'evaluation_methodology' => 'NET_BENEFIT',
                'is_active' => true,
            ]
        );

        return HcmProductivityRoiCalculation::create([
            'id' => Str::uuid()->toString(),
            'tenant_id' => $tenantId,
            'roi_model_id' => $model->id,
            'department_id' => $departmentId,
            'investment_name' => $roiData->investmentName,
            'investment_cost' => $roiData->investmentCost,
            'operational_benefit' => $roiData->operationalBenefit,
            'net_benefit' => $roiData->netBenefit,
            'roi_percentage' => $roiData->roiPercentage,
            'payback_period_months' => $roiData->paybackPeriodMonths,
            'causality_label' => $roiData->causalityLabel,
            'pre_investment_output_rate' => $roiData->preInvestmentRate,
            'post_investment_output_rate' => $roiData->postInvestmentRate,
            'calculation_details' => $roiData->details,
        ]);
    }

    /**
     * Compute Recruitment & Ramp-Up ROI.
     */
    public function calculateRecruitmentRoi(
        string $tenantId,
        string $roleName,
        float $recruitmentCost,
        float $rampUpMonths,
        float $standardMonthlyOutputValue,
        float $rampUpEfficiencyPct = 60.0, // e.g. average 60% during ramp-up
        ?string $departmentId = null
    ): HcmProductivityRoiCalculation {
        // Drag cost during ramp-up = rampUpMonths * standardMonthlyOutputValue * (1 - efficiency)
        $rampUpLoss = round($rampUpMonths * $standardMonthlyOutputValue * ((100.0 - $rampUpEfficiencyPct) / 100.0), 4);
        $totalHiringInvestment = round($recruitmentCost + $rampUpLoss, 4);

        // Annualized post-ramp output value (12 months of full efficiency)
        $grossBenefit = round(12.0 * $standardMonthlyOutputValue, 4);

        $roiData = $this->evaluateRoi(
            investmentName: "Recruitment: {$roleName}",
            investmentType: 'hiring',
            investmentCost: $totalHiringInvestment,
            operationalBenefit: $grossBenefit,
            causality: 'CORRELATION',
            details: [
                'direct_recruitment_cost' => $recruitmentCost,
                'ramp_up_months' => $rampUpMonths,
                'ramp_up_efficiency_pct' => $rampUpEfficiencyPct,
                'ramp_up_drag_cost' => $rampUpLoss,
            ]
        );

        $model = HcmProductivityRoiModel::firstOrCreate(
            ['tenant_id' => $tenantId, 'investment_type' => 'hiring'],
            [
                'id' => Str::uuid()->toString(),
                'model_name' => 'Default Recruitment ROI Model',
                'currency' => 'USD',
                'evaluation_methodology' => 'NET_BENEFIT',
                'is_active' => true,
            ]
        );

        return HcmProductivityRoiCalculation::create([
            'id' => Str::uuid()->toString(),
            'tenant_id' => $tenantId,
            'roi_model_id' => $model->id,
            'department_id' => $departmentId,
            'investment_name' => $roiData->investmentName,
            'investment_cost' => $roiData->investmentCost,
            'operational_benefit' => $roiData->operationalBenefit,
            'net_benefit' => $roiData->netBenefit,
            'roi_percentage' => $roiData->roiPercentage,
            'payback_period_months' => $roiData->paybackPeriodMonths,
            'causality_label' => $roiData->causalityLabel,
            'calculation_details' => $roiData->details,
        ]);
    }
}
