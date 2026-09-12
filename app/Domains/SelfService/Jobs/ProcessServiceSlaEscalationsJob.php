<?php

namespace App\Domains\SelfService\Jobs;

use App\Domains\SelfService\Enums\SlaStatus;
use App\Domains\SelfService\Events\ServiceRequestSlaBreached;
use App\Domains\SelfService\Models\HrServiceSlaInstance;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessServiceSlaEscalationsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $tenantId
    ) {}

    public function handle(): void
    {
        $now = now();

        $runningInstances = HrServiceSlaInstance::where('tenant_id', $this->tenantId)
            ->where('status', SlaStatus::RUNNING->value)
            ->with(['request'])
            ->get();

        foreach ($runningInstances as $instance) {
            $resolutionDue = $instance->resolution_due_at;

            if ($resolutionDue && $now->isAfter($resolutionDue) && !$instance->resolved_at) {
                $instance->update(['status' => SlaStatus::BREACHED->value]);

                if ($instance->request) {
                    event(new ServiceRequestSlaBreached($instance->request));
                }
            }
        }
    }
}
