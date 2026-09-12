<?php

namespace App\Domains\Benefits\Jobs;

use App\Domains\Benefits\Models\LoanApplication;
use App\Domains\Benefits\Services\LoanScheduleService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateLoanScheduleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public LoanApplication $application
    ) {}

    public function handle(LoanScheduleService $service): void
    {
        $app = $this->application;
        if ($app->approved_amount && $app->approved_tenure_months) {
            $service->generateSchedule(
                $app,
                (float) $app->approved_amount,
                (int) $app->approved_tenure_months,
                (float) $app->interest_rate,
                $app->interest_method,
                now()->toDateString()
            );
        }
    }
}
