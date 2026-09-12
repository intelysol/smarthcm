<?php

namespace App\Domains\Payroll\Jobs;

use App\Domains\Payroll\Models\PayrollRun;
use App\Domains\Payroll\Services\PaymentBatchService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GeneratePaymentBatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public PayrollRun $run, public array $data = []) {}

    public function handle(PaymentBatchService $paymentService): void
    {
        $paymentService->createPaymentBatch($this->run, $this->data);
    }
}
