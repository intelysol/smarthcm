<?php

namespace App\Domains\WorkforceAdmin\Jobs;

use App\Domains\WorkforceAdmin\Events\HrSlaBreached;
use App\Domains\WorkforceAdmin\Models\OpsSlaInstance;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckSlaBreachesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $tenantId
    ) {}

    public function handle(): void
    {
        $now = now();

        $runningInstances = OpsSlaInstance::where('tenant_id', $this->tenantId)
            ->where('status', 'running')
            ->where('is_breached', false)
            ->get();

        foreach ($runningInstances as $instance) {
            $responseBreached = $instance->response_due_at && !$instance->first_response_at && $now->isAfter($instance->response_due_at);
            $resolutionBreached = $now->isAfter($instance->resolution_due_at);

            if ($responseBreached || $resolutionBreached) {
                $instance->update(['is_breached' => true]);
                event(new HrSlaBreached($instance));
            }
        }
    }
}
