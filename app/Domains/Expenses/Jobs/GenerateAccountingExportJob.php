<?php

namespace App\Domains\Expenses\Jobs;

use App\Domains\Expenses\Services\ExpenseAccountingService;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateAccountingExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $tenantId,
        public string $startDate,
        public string $endDate,
        public User $user
    ) {}

    public function handle(ExpenseAccountingService $accountingService): void
    {
        $accountingService->generateAccountingExport(
            $this->tenantId,
            $this->startDate,
            $this->endDate,
            $this->user
        );
    }
}
