<?php

namespace App\Domains\SelfService\Jobs;

use App\Domains\SelfService\Services\OmnichannelIntakeService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class IngestOmnichannelMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $tenantId,
        public string $channel,
        public array $payload
    ) {}

    public function handle(OmnichannelIntakeService $service): void
    {
        $service->ingestMessage($this->tenantId, $this->channel, $this->payload);
    }
}
