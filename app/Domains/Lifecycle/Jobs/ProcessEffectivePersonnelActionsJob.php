<?php

namespace App\Domains\Lifecycle\Jobs;

use App\Domains\Lifecycle\Enums\PersonnelActionStatus;
use App\Domains\Lifecycle\Models\PersonnelActionRequest;
use App\Domains\Lifecycle\Services\PersonnelActionExecutionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessEffectivePersonnelActionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(PersonnelActionExecutionService $executionService): void
    {
        $dueActions = PersonnelActionRequest::where('status', PersonnelActionStatus::SCHEDULED->value)
            ->whereDate('effective_date', '<=', now()->toDateString())
            ->get();

        foreach ($dueActions as $action) {
            try {
                $executionService->execute($action);
            } catch (\Exception $e) {
                $action->update([
                    'status' => PersonnelActionStatus::FAILED->value,
                    'comments' => 'Execution error: ' . $e->getMessage(),
                ]);
            }
        }
    }
}
