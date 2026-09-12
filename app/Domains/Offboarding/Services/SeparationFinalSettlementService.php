<?php

namespace App\Domains\Offboarding\Services;

use App\Domains\Offboarding\Enums\SettlementStatus;
use App\Domains\Offboarding\Models\SeparationAudit;
use App\Domains\Offboarding\Models\SeparationFinalSettlement;
use App\Domains\Offboarding\Models\SeparationRequest;
use App\Models\User;

class SeparationFinalSettlementService
{
    public function recordSettlementSnapshot(SeparationRequest $request, array $data, User $approver): SeparationFinalSettlement
    {
        $gross = (float) ($data['gross_payable'] ?? 0);
        $deductions = (float) ($data['deductions'] ?? 0);
        $net = $gross - $deductions;

        $settlement = SeparationFinalSettlement::updateOrCreate(
            [
                'separation_request_id' => $request->id,
            ],
            [
                'tenant_id' => $request->tenant_id,
                'payroll_run_id' => $data['payroll_run_id'] ?? null,
                'gross_payable' => $gross,
                'deductions' => $deductions,
                'net_payable' => $net,
                'currency' => $data['currency'] ?? 'USD',
                'settlement_status' => SettlementStatus::APPROVED->value,
                'approved_at' => now(),
                'approved_by' => $approver->id,
                'payment_date' => $data['payment_date'] ?? null,
                'snapshot_data' => $data['snapshot_data'] ?? [
                    'unpaid_salary' => $gross,
                    'leave_encashment' => 0.00,
                    'loan_deductions' => $deductions,
                ],
            ]
        );

        SeparationAudit::create([
            'tenant_id' => $request->tenant_id,
            'separation_request_id' => $request->id,
            'actor_id' => $approver->id,
            'event_name' => 'settlement_approved',
            'new_state' => ['gross' => $gross, 'deductions' => $deductions, 'net' => $net],
            'reason' => 'Final settlement statement reviewed and authorized',
        ]);

        return $settlement;
    }
}
