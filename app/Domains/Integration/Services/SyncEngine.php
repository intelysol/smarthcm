<?php

declare(strict_types=1);

namespace App\Domains\Integration\Services;

use App\Domains\Integration\Jobs\InboundSyncJob;
use App\Domains\Integration\Jobs\OutboundIntegrationJob;
use App\Domains\Integration\Models\IntegrationConnection;
use App\Domains\Integration\Models\IntegrationSyncRun;
use InvalidArgumentException;

class SyncEngine
{
    /**
     * Trigger a scheduled pull or batch sync run.
     */
    public function triggerScheduledSync(
        string $connectionId,
        string $direction = 'inbound',
        string $entityType = 'employee'
    ): IntegrationSyncRun {
        $connection = IntegrationConnection::findOrFail($connectionId);

        $syncRun = IntegrationSyncRun::create([
            'connection_id' => $connection->id,
            'sync_type' => $direction,
            'status' => 'pending',
            'processed' => 0,
            'failed' => 0,
            'started_at' => now(),
        ]);

        if ($direction === 'inbound') {
            InboundSyncJob::dispatch([
                'connection_id' => $connection->id,
                'sync_run_id' => $syncRun->id,
                'entity_type' => $entityType,
            ]);
        } else {
            OutboundIntegrationJob::dispatch([
                'connection_id' => $connection->id,
                'sync_run_id' => $syncRun->id,
                'payload' => [],
            ]);
        }

        return $syncRun;
    }

    /**
     * Trigger an event-driven sync run (e.g. from an incoming webhook or internal event).
     */
    public function triggerEventSync(
        string $connectionId,
        array $payload,
        string $direction = 'inbound',
        string $entityType = 'employee'
    ): IntegrationSyncRun {
        $connection = IntegrationConnection::findOrFail($connectionId);

        $syncRun = IntegrationSyncRun::create([
            'connection_id' => $connection->id,
            'sync_type' => $direction,
            'status' => 'running',
            'processed' => 0,
            'failed' => 0,
            'started_at' => now(),
        ]);

        if ($direction === 'inbound') {
            InboundSyncJob::dispatch([
                'connection_id' => $connection->id,
                'sync_run_id' => $syncRun->id,
                'entity_type' => $entityType,
                'payload' => $payload,
            ]);
        } else {
            OutboundIntegrationJob::dispatch([
                'connection_id' => $connection->id,
                'sync_run_id' => $syncRun->id,
                'payload' => $payload,
            ]);
        }

        return $syncRun;
    }
}
