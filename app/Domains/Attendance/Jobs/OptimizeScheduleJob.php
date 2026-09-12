<?php

namespace App\Domains\Attendance\Jobs;

use App\Domains\Attendance\Models\RosterPeriod;
use App\Domains\Attendance\Services\Scheduling\ScheduleOptimizationService;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class OptimizeScheduleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public RosterPeriod $period,
        public string $strategy = 'rule_based_heuristic',
        public ?User $actor = null
    ) {}

    public function handle(ScheduleOptimizationService $optimizationService): void
    {
        $optimizationService->optimize($this->period, $this->strategy, $this->actor);
    }
}
