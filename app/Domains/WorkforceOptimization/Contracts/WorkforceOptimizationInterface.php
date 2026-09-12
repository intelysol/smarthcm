<?php

namespace App\Domains\WorkforceOptimization\Contracts;

use App\Domains\WorkforceOptimization\Models\HcmWorkforceOptimizationRun;

interface WorkforceOptimizationInterface
{
    /**
     * Orchestrate full end-to-end optimization run for a tenant.
     */
    public function executeOptimizationRun(string $tenantId, ?string $modelId = null, ?int $userId = null): HcmWorkforceOptimizationRun;

    /**
     * Run full workforce optimization cycle.
     */
    public function runOptimization(
        string $tenantId,
        ?string $modelCode = null,
        array $scope = [],
        ?\App\Models\User $triggeredBy = null
    ): HcmWorkforceOptimizationRun;

    /**
     * Run what-if scenario simulation.
     */
    public function simulateScenario(
        HcmWorkforceOptimizationRun $run,
        string $scenarioCode,
        string $name,
        array $parameters = []
    ): \App\Domains\WorkforceOptimization\Models\HcmWorkforceOptimizationScenario;

    /**
     * Compare scenarios side-by-side.
     */
    public function compareScenarios(array $scenarios): \App\Domains\WorkforceOptimization\DTOs\ScenarioComparisonData;
}
