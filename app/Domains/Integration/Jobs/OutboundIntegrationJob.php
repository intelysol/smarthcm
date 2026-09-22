<?php

declare(strict_types=1);

namespace App\Domains\Integration\Jobs;

use App\Domains\Integration\Models\IntegrationConnection;
use App\Domains\Integration\Models\IntegrationMapping;
use App\Domains\Integration\Models\IntegrationSyncRun;
use App\Domains\Integration\Services\CredentialManager;
use App\Domains\Integration\Services\DeadLetterService;
use App\Domains\Integration\Services\MappingEngine;
use Flow\Packages\Integrations\Infrastructure\Services\ConnectorRegistry;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class OutboundIntegrationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public array $params
    ) {}

    public function handle(
        ConnectorRegistry $registry,
        CredentialManager $credentialManager,
        MappingEngine $mappingEngine,
        DeadLetterService $deadLetterService
    ): void {
        $connectionId = $this->params['connection_id'] ?? null;
        $syncRunId = $this->params['sync_run_id'] ?? null;
        $payload = $this->params['payload'] ?? [];

        $connection = IntegrationConnection::with('connector')->find($connectionId);
        if (!$connection) {
            Log::error("OutboundIntegrationJob: Connection [{$connectionId}] not found.");
            return;
        }

        $syncRun = $syncRunId ? IntegrationSyncRun::find($syncRunId) : null;
        if ($syncRun) {
            $syncRun->status = 'running';
            $syncRun->save();
        }

        try {
            $mapping = IntegrationMapping::where('connection_id', $connectionId)->first();
            $transformed = $mapping
                ? $mappingEngine->transform($payload, $mapping, 'outbound')
                : $payload;

            $connector = $registry->get($connection->connector->key);
            $credentials = $credentialManager->resolveCredentials($connection->id);

            $result = $connector->push($transformed, $credentials, $connection->configuration ?? []);

            if (!$result->success) {
                throw new \RuntimeException("Outbound push failed: " . $result->errorMessage);
            }

            if ($syncRun) {
                $syncRun->processed = 1;
                $syncRun->failed = 0;
                $syncRun->status = 'completed';
                $syncRun->completed_at = now();
                $syncRun->save();
            }
        } catch (Throwable $e) {
            if ($syncRun) {
                $syncRun->status = 'failed';
                $syncRun->error_message = $e->getMessage();
                $syncRun->completed_at = now();
                $syncRun->save();
            }

            $deadLetterService->recordDeadLetter(
                $connectionId,
                'outbound_sync',
                $this->params,
                $e->getMessage(),
                $this->attempts(),
                $connection->tenant_id
            );

            throw $e;
        }
    }
}
