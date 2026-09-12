<?php

namespace App\Console\Commands;

use App\Domains\EmployeeRelations\Jobs\EvaluateCaseSlaJob;
use App\Domains\EmployeeRelations\Models\EmployeeRelationCase;
use App\Domains\EmployeeRelations\Models\EmployeeRelationCorrectiveAction;
use App\Domains\EmployeeRelations\Models\EmployeeRelationInvestigation;
use Illuminate\Console\Command;

class EmployeeRelationsDailyCheckCommand extends Command
{
    protected $signature = 'hcm:er-daily-check';
    protected $description = 'Perform daily Employee Relations SLA evaluations, deadline checks, and overdue notifications';

    public function handle(): int
    {
        $this->info('Running ER SLA evaluations...');
        dispatch_sync(new EvaluateCaseSlaJob());

        $this->info('Checking overdue corrective actions...');
        EmployeeRelationCorrectiveAction::query()
            ->whereIn('status', ['assigned', 'in_progress'])
            ->where('due_date', '<', now()->toDateString())
            ->update(['status' => 'overdue']);

        $this->info('Checking overdue investigation steps...');
        EmployeeRelationInvestigation::query()
            ->where('status', 'active')
            ->where('target_completion_date', '<', now()->toDateString())
            ->get();

        $this->info('ER daily scheduled checks completed.');

        return Command::SUCCESS;
    }
}
