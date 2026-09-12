<?php

namespace App\Console\Commands;

use App\Domains\WorkforceProductivity\Services\WorkforceROIService;
use Illuminate\Console\Command;

class HcmCalculateWorkforceRoiCommand extends Command
{
    protected $signature = 'hcm:productivity-roi {--tenant-id= : Tenant UUID} {--program= : Program Name} {--cost= : Direct Investment Cost} {--benefit= : Operational Benefit} {--causality=CORRELATION : Causality Label}';
    protected $description = 'Evaluate and persist Workforce ROI calculation';

    public function handle(WorkforceROIService $service): int
    {
        $tenantId = $this->option('tenant-id');
        $program = $this->option('program') ?? 'Workforce Initiative';
        $cost = (float) $this->option('cost');
        $benefit = (float) $this->option('benefit');
        $causality = $this->option('causality') ?? 'CORRELATION';

        if (! $tenantId || $cost <= 0) {
            $this->error('The --tenant-id and valid --cost are required.');
            return self::FAILURE;
        }

        $roiData = $service->evaluateRoi(
            investmentName: $program,
            investmentType: 'training',
            investmentCost: $cost,
            operationalBenefit: $benefit,
            causality: $causality
        );

        $this->info("ROI evaluated for {$program}: {$roiData->roiPercentage}% (Net Benefit: \${$roiData->netBenefit}, Causality: {$roiData->causalityLabel})");
        return self::SUCCESS;
    }
}
