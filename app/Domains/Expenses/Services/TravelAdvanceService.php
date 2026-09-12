<?php

namespace App\Domains\Expenses\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\Expenses\Enums\AdvanceStatus;
use App\Domains\Expenses\Enums\SettlementType;
use App\Domains\Expenses\Models\ExpenseClaim;
use App\Domains\Expenses\Models\TravelAdvance;
use App\Domains\Expenses\Models\TravelAdvanceDisbursement;
use App\Domains\Expenses\Models\TravelAdvanceSettlement;
use App\Domains\Expenses\Models\TravelRequest;
use App\Domains\Shared\Services\AuditService;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TravelAdvanceService
{
    protected AuditService $auditService;

    public function __construct(?AuditService $auditService = null)
    {
        $this->auditService = $auditService ?? app(AuditService::class);
    }

    public function requestAdvance(Employee $employee, array $data, ?TravelRequest $travelRequest = null): TravelAdvance
    {
        $advanceNumber = 'ADV-' . date('Y') . '-' . strtoupper(substr(uniqid(), -6));

        $advance = TravelAdvance::create([
            'tenant_id' => $employee->tenant_id,
            'employee_id' => $employee->id,
            'travel_request_id' => $travelRequest?->id ?? ($data['travel_request_id'] ?? null),
            'advance_number' => $advanceNumber,
            'requested_amount' => $data['requested_amount'],
            'approved_amount' => 0.0000,
            'disbursed_amount' => 0.0000,
            'settled_amount' => 0.0000,
            'currency' => $data['currency'] ?? 'USD',
            'purpose' => $data['purpose'] ?? 'Travel & Business Advance',
            'status' => AdvanceStatus::REQUESTED->value,
        ]);

        $this->auditService->record(
            tenantId: $employee->tenant_id,
            eventType: 'travel_advance.requested',
            action: 'create',
            entityType: 'TravelAdvance',
            entityId: $advance->id,
            after: ['advance_number' => $advanceNumber, 'requested_amount' => $advance->requested_amount, 'currency' => $advance->currency]
        );

        return $advance;
    }

    public function approveAdvance(TravelAdvance $advance, float $approvedAmount, User $approver): TravelAdvance
    {
        $oldStatus = $advance->status;
        $advance->update([
            'approved_amount' => $approvedAmount,
            'status' => AdvanceStatus::APPROVED->value,
            'approved_by' => $approver->id,
            'approved_at' => now(),
        ]);

        $this->auditService->record(
            tenantId: $advance->tenant_id,
            eventType: 'travel_advance.approved',
            action: 'update',
            entityType: 'TravelAdvance',
            entityId: $advance->id,
            actorId: is_numeric($approver->id) ? (int)$approver->id : null,
            before: ['status' => $oldStatus],
            after: ['status' => AdvanceStatus::APPROVED->value, 'approved_amount' => $approvedAmount]
        );

        return $advance->fresh();
    }

    public function disburseAdvance(TravelAdvance $advance, array $disbursementData, User $disburser): TravelAdvanceDisbursement
    {
        return DB::transaction(function () use ($advance, $disbursementData, $disburser) {
            $amount = (float) ($disbursementData['disbursed_amount'] ?? $advance->approved_amount);
            $disbNumber = 'DISB-' . date('Y') . '-' . strtoupper(substr(uniqid(), -6));

            $disbursement = TravelAdvanceDisbursement::create([
                'tenant_id' => $advance->tenant_id,
                'travel_advance_id' => $advance->id,
                'disbursement_number' => $disbNumber,
                'disbursed_amount' => $amount,
                'currency' => $advance->currency,
                'disbursement_date' => $disbursementData['disbursement_date'] ?? now()->toDateString(),
                'payment_method' => $disbursementData['payment_method'] ?? 'bank_transfer',
                'finance_reference' => $disbursementData['finance_reference'] ?? null,
                'disbursed_by' => $disburser->id,
                'notes' => $disbursementData['notes'] ?? null,
            ]);

            $newDisbursedTotal = (float) $advance->disbursed_amount + $amount;
            $advance->update([
                'disbursed_amount' => $newDisbursedTotal,
                'status' => AdvanceStatus::DISBURSED->value,
            ]);

            $this->auditService->record(
                tenantId: $advance->tenant_id,
                eventType: 'travel_advance.disbursed',
                action: 'create',
                entityType: 'TravelAdvanceDisbursement',
                entityId: $disbursement->id,
                actorId: is_numeric($disburser->id) ? (int)$disburser->id : null,
                after: ['advance_id' => $advance->id, 'disbursed_amount' => $amount, 'payment_method' => $disbursement->payment_method]
            );

            return $disbursement;
        });
    }

    public function settleAdvanceAgainstClaim(TravelAdvance $advance, ExpenseClaim $claim, float $settleAmount): TravelAdvanceSettlement
    {
        return DB::transaction(function () use ($advance, $claim, $settleAmount) {
            $unsettled = $advance->remainingUnsettledAmount();
            $appliedAmount = min($unsettled, $settleAmount);

            $newSettledTotal = (float) $advance->settled_amount + $appliedAmount;
            $remaining = max(0.0, (float) $advance->disbursed_amount - $newSettledTotal);

            $settlement = TravelAdvanceSettlement::create([
                'tenant_id' => $advance->tenant_id,
                'travel_advance_id' => $advance->id,
                'expense_claim_id' => $claim->id,
                'settled_amount' => $appliedAmount,
                'remaining_balance' => $remaining,
                'settlement_date' => now()->toDateString(),
                'settlement_type' => SettlementType::CLAIM_OFFSET->value,
                'finance_reference' => "Offset against Claim {$claim->claim_number}",
            ]);

            $status = ($remaining <= 0.0001) ? AdvanceStatus::SETTLED->value : AdvanceStatus::DISBURSED->value;
            $advance->update([
                'settled_amount' => $newSettledTotal,
                'status' => $status,
            ]);

            // Update Claim advance settled amount
            $claim->update([
                'advance_settled_amount' => (float) $claim->advance_settled_amount + $appliedAmount,
            ]);
            $claim->recalculateTotals();

            $this->auditService->record(
                tenantId: $advance->tenant_id,
                eventType: 'travel_advance.settled_against_claim',
                action: 'create',
                entityType: 'TravelAdvanceSettlement',
                entityId: $settlement->id,
                after: ['advance_id' => $advance->id, 'claim_id' => $claim->id, 'settled_amount' => $appliedAmount, 'remaining_balance' => $remaining]
            );

            return $settlement;
        });
    }

    public function settleRemainingWithRefundOrRecovery(TravelAdvance $advance, string $settlementType, ?string $financeRef = null): TravelAdvanceSettlement
    {
        return DB::transaction(function () use ($advance, $settlementType, $financeRef) {
            $unsettled = $advance->remainingUnsettledAmount();
            if ($unsettled <= 0.0) {
                throw ValidationException::withMessages([
                    'advance' => 'Advance is already fully settled.',
                ]);
            }

            $newSettledTotal = (float) $advance->settled_amount + $unsettled;

            $settlement = TravelAdvanceSettlement::create([
                'tenant_id' => $advance->tenant_id,
                'travel_advance_id' => $advance->id,
                'expense_claim_id' => null,
                'settled_amount' => $unsettled,
                'remaining_balance' => 0.0000,
                'settlement_date' => now()->toDateString(),
                'settlement_type' => $settlementType,
                'finance_reference' => $financeRef ?? "Manual balance settlement: {$settlementType}",
            ]);

            $advance->update([
                'settled_amount' => $newSettledTotal,
                'status' => AdvanceStatus::SETTLED->value,
            ]);

            $this->auditService->record(
                tenantId: $advance->tenant_id,
                eventType: 'travel_advance.settled_refund_or_recovery',
                action: 'create',
                entityType: 'TravelAdvanceSettlement',
                entityId: $settlement->id,
                after: ['advance_id' => $advance->id, 'settlement_type' => $settlementType, 'settled_amount' => $unsettled]
            );

            return $settlement;
        });
    }

    public function getOutstandingAdvanceBalance(Employee $employee): float
    {
        return (float) TravelAdvance::query()
            ->where('tenant_id', $employee->tenant_id)
            ->where('employee_id', $employee->id)
            ->whereIn('status', [AdvanceStatus::DISBURSED->value, AdvanceStatus::PARTIALLY_DISBURSED->value, AdvanceStatus::OVERDUE->value])
            ->get()
            ->sum(fn ($adv) => $adv->remainingUnsettledAmount());
    }
}
