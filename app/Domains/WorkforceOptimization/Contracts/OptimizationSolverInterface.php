<?php

namespace App\Domains\WorkforceOptimization\Contracts;

interface OptimizationSolverInterface
{
    /**
     * Solve multi-objective optimization problem against candidates, objectives, and constraints.
     * Returns scored and ranked recommendations.
     */
    public function solve(
        \App\Domains\WorkforceOptimization\DTOs\OptimizationInputSnapshotData $snapshot,
        ?\App\Domains\WorkforceOptimization\Models\HcmWorkforceOptimizationModelVersion $modelVersion = null,
        array $options = []
    ): array;

    /**
     * Perform Pareto frontier trade-off evaluation on multiple scenario options.
     */
    public function evaluatePareto(array $scenarioOptions): array;
}
