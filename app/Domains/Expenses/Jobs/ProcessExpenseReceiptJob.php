<?php

namespace App\Domains\Expenses\Jobs;

use App\Domains\Expenses\Models\ExpenseReceipt;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessExpenseReceiptJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public ExpenseReceipt $receipt) {}

    public function handle(): void
    {
        // Simulated OCR extraction and hash validation
        if (empty($this->receipt->receipt_hash)) {
            $this->receipt->update([
                'receipt_hash' => hash('sha256', $this->receipt->file_name . $this->receipt->file_size),
                'status' => 'verified',
            ]);
        }
    }
}
