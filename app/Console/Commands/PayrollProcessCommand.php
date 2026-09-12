<?php

namespace App\Console\Commands;

use App\Domains\Payroll\Models\PayrollPeriod;
use App\Domains\Payroll\Models\PayrollRun;
use App\Domains\Payroll\Services\PayrollRunService;
use Illuminate\Console\Command;

class PayrollProcessCommand extends Command
{
    protected $signature = 'hcm:payroll-process {run_id? : Optional specific Payroll Run UUID}';
    protected $description = 'Process and calculate draft payroll runs for active periods';

    public function handle(PayrollRunService $runService): int
    {
        $runId = $this->argument('run_id');

        if ($runId) {
            $run = PayrollRun::query()->find($runId);
            if (! $run) {
                $this->error("Payroll Run [{$runId}] not found.");
                return self::FAILURE;
            }

            $this->info("Calculating Payroll Run: {$run->run_number} ({$run->name})...");
            $runService->calculateRun($run);
            $this->info("Completed. Gross: \${$run->gross_total}, Net: \${$run->net_total} for {$run->employee_count} employees.");
            return self::SUCCESS;
        }

        $draftRuns = PayrollRun::query()
            ->whereIn('status', ['draft', 'validation_failed'])
            ->whereNull('locked_at')
            ->get();

        $this->info("Found {$draftRuns->count()} draft payroll runs to process.");

        foreach ($draftRuns as $r) {
            $this->line("Processing Run: {$r->run_number}...");
            $runService->calculateRun($r);
        }

        $this->info('Payroll batch processing complete.');
        return self::SUCCESS;
    }
}
