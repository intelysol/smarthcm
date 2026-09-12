<?php

namespace App\Domains\WorkforcePlanning\Jobs;

use App\Domains\WorkforcePlanning\Models\HcmWorkforceScenario;
use App\Domains\WorkforcePlanning\Services\WorkforceScenarioService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CalculateWorkforceScenarioJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public string $scenarioId)
    {
    }

    public function handle(WorkforceScenarioService $scenarioService): void
    {
        $scenario = HcmWorkforceScenario::find($this->scenarioId);
        if ($scenario) {
            $scenarioService->simulateScenario($scenario);
        }
    }
}
