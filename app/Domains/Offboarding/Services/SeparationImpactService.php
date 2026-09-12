<?php

namespace App\Domains\Offboarding\Services;

use App\Domains\Offboarding\Enums\ImpactSeverity;
use App\Domains\Offboarding\Models\SeparationImpact;
use App\Domains\Offboarding\Models\SeparationRequest;

class SeparationImpactService
{
    public function analyzeImpact(SeparationRequest $request): array
    {
        // Clear prior impacts
        SeparationImpact::where('separation_request_id', $request->id)->delete();

        $impacts = [];
        $employee = $request->employee;

        // 1. Payroll Impact (Estimated final salary settlement)
        $impacts[] = SeparationImpact::create([
            'tenant_id' => $request->tenant_id,
            'separation_request_id' => $request->id,
            'domain' => 'payroll',
            'impact_type' => 'final_pay_calculation',
            'severity' => ImpactSeverity::WARNING->value,
            'message' => 'Final salary settlement requires prorated calculation up to Approved Last Working Day and deduction adjustments.',
            'metadata' => ['effective_date' => $request->effective_date->toDateString()],
        ]);

        // 2. Leave Settlement Impact
        $impacts[] = SeparationImpact::create([
            'tenant_id' => $request->tenant_id,
            'separation_request_id' => $request->id,
            'domain' => 'leave',
            'impact_type' => 'leave_encashment',
            'severity' => ImpactSeverity::INFO->value,
            'message' => 'Accrued unused annual leave balance must be verified for final encashment or deduction per company policy.',
        ]);

        // 3. Asset & Equipment Return Impact
        $impacts[] = SeparationImpact::create([
            'tenant_id' => $request->tenant_id,
            'separation_request_id' => $request->id,
            'domain' => 'assets',
            'impact_type' => 'unreturned_equipment',
            'severity' => ImpactSeverity::BLOCKING->value,
            'message' => 'Company-issued IT laptop, mobile device, and security access badge must be surrendered prior to exit clearance.',
        ]);

        // 4. Employee Relations Reference Check (Confidentiality Protection)
        if (!empty($request->er_case_reference_id)) {
            $impacts[] = SeparationImpact::create([
                'tenant_id' => $request->tenant_id,
                'separation_request_id' => $request->id,
                'domain' => 'er',
                'impact_type' => 'open_er_case',
                'severity' => ImpactSeverity::WARNING->value,
                'message' => sprintf(
                    'Active Employee Relations case referenced (%s). Compliance sign-off required prior to final settlement.',
                    $request->er_case_reference_id
                ),
                'metadata' => ['case_reference' => $request->er_case_reference_id],
            ]);
        }

        return $impacts;
    }
}
