<?php

namespace App\Domains\Attendance\Jobs;

use App\Domains\Attendance\Models\RosterPeriod;
use App\Domains\Attendance\Services\Scheduling\ScheduleValidationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ValidateScheduleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public RosterPeriod $period
    ) {}

    public function handle(ScheduleValidationService $validationService): void
    {
        $validationService->validatePeriod($this->period);
    }
}
