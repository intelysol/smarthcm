<?php

namespace App\Domains\WorkforceProductivity\Contracts;

use App\Domains\WorkforceProductivity\DTOs\WorkforceRoiData;

interface WorkforceRoiEvaluatorInterface
{
    /**
     * Evaluate workforce investment ROI: (Net Benefit - Investment Cost) / Cost.
     */
    public function evaluateRoi(
        string $investmentName,
        string $investmentType,
        float $investmentCost,
        float $operationalBenefit,
        string $causality = 'CORRELATION',
        ?float $preRate = null,
        ?float $postRate = null,
        array $details = []
    ): WorkforceRoiData;
}
