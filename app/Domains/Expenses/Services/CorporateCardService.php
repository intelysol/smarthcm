<?php

namespace App\Domains\Expenses\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\Expenses\Models\CorporateCard;
use App\Domains\Expenses\Models\CorporateCardMatch;
use App\Domains\Expenses\Models\CorporateCardTransaction;
use App\Domains\Expenses\Models\ExpenseClaimLine;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CorporateCardService
{
    public function assignCard(Employee $employee, array $cardData): CorporateCard
    {
        $rawNumber = $cardData['card_number'] ?? '4111111111111234';
        $masked = 'XXXX-XXXX-XXXX-' . substr($rawNumber, -4);
        $token = 'CARD-TOK-' . strtoupper(uniqid());

        return CorporateCard::create([
            'tenant_id' => $employee->tenant_id,
            'employee_id' => $employee->id,
            'card_token' => $token,
            'card_masked_number' => $masked,
            'card_holder_name' => $cardData['card_holder_name'] ?? $employee->fullName(),
            'card_provider' => $cardData['card_provider'] ?? 'Visa',
            'expiry_date' => $cardData['expiry_date'] ?? now()->addYears(3)->toDateString(),
            'status' => 'active',
        ]);
    }

    public function importTransaction(CorporateCard $card, array $data): CorporateCardTransaction
    {
        $ref = $data['transaction_reference'] ?? ('TXN-' . strtoupper(uniqid()));
        $amount = (float) $data['amount'];
        $currency = $data['currency'] ?? 'USD';
        $baseCurrency = $data['base_currency'] ?? 'USD';
        $exchangeRate = (float) ($data['exchange_rate'] ?? 1.0);
        $baseAmount = round($amount * $exchangeRate, 4);

        return CorporateCardTransaction::create([
            'tenant_id' => $card->tenant_id,
            'corporate_card_id' => $card->id,
            'employee_id' => $card->employee_id,
            'transaction_reference' => $ref,
            'transaction_date' => $data['transaction_date'] ?? now(),
            'merchant_name' => $data['merchant_name'],
            'amount' => $amount,
            'currency' => $currency,
            'base_amount' => $baseAmount,
            'base_currency' => $baseCurrency,
            'exchange_rate' => $exchangeRate,
            'category_hint' => $data['category_hint'] ?? null,
            'is_matched' => false,
            'match_status' => 'unmatched',
        ]);
    }

    public function matchTransactionToClaimLine(CorporateCardTransaction $transaction, ExpenseClaimLine $claimLine, User $matchedBy): CorporateCardMatch
    {
        return DB::transaction(function () use ($transaction, $claimLine, $matchedBy) {
            $match = CorporateCardMatch::create([
                'tenant_id' => $transaction->tenant_id,
                'corporate_card_transaction_id' => $transaction->id,
                'expense_claim_line_id' => $claimLine->id,
                'matched_amount' => $transaction->amount,
                'match_confidence' => 100.00,
                'matched_by' => $matchedBy->id,
                'matched_at' => now(),
            ]);

            $transaction->update([
                'is_matched' => true,
                'matched_claim_line_id' => $claimLine->id,
                'match_status' => 'matched',
            ]);

            $claimLine->update([
                'corporate_card_transaction_id' => $transaction->id,
                'payment_method' => 'corporate_card',
            ]);

            return $match;
        });
    }
}
