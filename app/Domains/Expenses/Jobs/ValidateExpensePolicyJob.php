<?php

namespace App\Domains\Expenses\Jobs;

use App\Domains\Expenses\Models\ExpenseClaim;
use App\Domains\Expenses\Services\ExpensePolicyService;
use App\Domains\Expenses\Services\ExpenseValidationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ValidateExpensePolicyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public ExpenseClaim $claim) {}

    public function handle(ExpensePolicyService $policyService, ExpenseValidationService $validationService): void
    {
        $policy = $this->claim->policy;
        $version = $policy ? $policyService->getActiveVersionForDate($policy, $this->claim->claim_date?->toDateString()) : null;

        foreach ($this->claim->lines as $line) {
            $validationService->validateClaimLine($line, $version);
        }

        $this->claim->recalculateTotals();
    }
}
