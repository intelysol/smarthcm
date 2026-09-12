<?php

namespace App\Domains\WorkforcePlanning\Jobs;

use App\Domains\WorkforcePlanning\Services\WorkforceScenarioService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CompareWorkforceScenariosJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public array $scenarioIds)
    {
    }

    public function handle(WorkforceScenarioService $scenarioService): array
    {
        return $scenarioService->compareScenarios($this->scenarioIds);
    }
}
