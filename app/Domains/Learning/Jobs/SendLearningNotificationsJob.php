<?php

namespace App\Domains\Learning\Jobs;

use App\Domains\Communication\Services\CommunicationService;
use App\Domains\Platform\Contracts\TenantContext;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendLearningNotificationsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly string $tenantId,
        public readonly array $notificationData
    ) {}

    public function handle(CommunicationService $communicationService, TenantContext $tenantContext): void
    {
        $tenant = Tenant::query()->find($this->tenantId);
        if (! $tenant) return;
        $tenantContext->set($tenant);

        $communicationService->queue($this->tenantId, $this->notificationData);
    }
}
