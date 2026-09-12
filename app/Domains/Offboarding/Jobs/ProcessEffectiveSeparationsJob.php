<?php

namespace App\Domains\Offboarding\Jobs;

use App\Domains\Offboarding\Enums\SeparationStatus;
use App\Domains\Offboarding\Models\SeparationRequest;
use App\Domains\Offboarding\Services\SeparationExecutionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessEffectiveSeparationsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(SeparationExecutionService $executionService): void
    {
        $dueSeparations = SeparationRequest::whereIn('status', [
                SeparationStatus::APPROVED->value,
                SeparationStatus::NOTICE_PERIOD->value,
                SeparationStatus::CLEARANCE->value,
                SeparationStatus::READY_FOR_EXIT->value,
            ])
            ->whereDate('effective_date', '<=', now()->toDateString())
            ->get();

        foreach ($dueSeparations as $request) {
            try {
                $executionService->execute($request);
            } catch (\Exception $e) {
                $request->update([
                    'status' => SeparationStatus::FAILED->value,
                    'comments' => 'Execution error: ' . $e->getMessage(),
                ]);
            }
        }
    }
}
