<?php

namespace App\Domains\Expenses\Jobs;

use App\Domains\Expenses\Models\CorporateCardTransaction;
use App\Domains\Expenses\Models\ExpenseClaimLine;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessCorporateCardTransactionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public string $tenantId) {}

    public function handle(): void
    {
        $unmatched = CorporateCardTransaction::query()
            ->where('tenant_id', $this->tenantId)
            ->where('is_matched', false)
            ->get();

        foreach ($unmatched as $tx) {
            // Check for matching claim line
            $match = ExpenseClaimLine::query()
                ->where('tenant_id', $this->tenantId)
                ->where('base_amount', $tx->base_amount)
                ->whereDate('expense_date', $tx->transaction_date->toDateString())
                ->whereNull('corporate_card_transaction_id')
                ->first();

            if ($match) {
                $match->update(['corporate_card_transaction_id' => $tx->id, 'payment_method' => 'corporate_card']);
                $tx->update(['is_matched' => true, 'matched_claim_line_id' => $match->id, 'match_status' => 'matched']);
            }
        }
    }
}
