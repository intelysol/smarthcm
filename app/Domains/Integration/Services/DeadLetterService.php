<?php

declare(strict_types=1);

namespace App\Domains\Integration\Services;

use App\Domains\Integration\Jobs\InboundSyncJob;
use App\Domains\Integration\Jobs\OutboundIntegrationJob;
use App\Domains\Integration\Jobs\WebhookDeliveryJob;
use App\Domains\Integration\Models\IntegrationDeadLetter;
use App\Domains\Platform\Models\PlatformNotification;
use App\Domains\Shared\Services\ActivityLogService;
use Illuminate\Support\Facades\Log;
use Throwable;

class DeadLetterService
{
    public function __construct(
        protected ActivityLogService $activityLogService
    ) {}

    /**
     * Record a failed job into integration_dead_letters, dispatch alert notification.
     */
    public function recordDeadLetter(
        ?string $connectionId,
        string $jobType,
        array $payload,
        string $errorMessage,
        int $attempts = 1,
        ?string $tenantId = null
    ): IntegrationDeadLetter {
        $deadLetter = IntegrationDeadLetter::create([
            'connection_id' => $connectionId,
            'job_type' => $jobType,
            'payload' => $payload,
            'error_message' => $errorMessage,
            'attempts' => $attempts,
            'failed_at' => now(),
        ]);

        // Send Platform Notification if tenantId is available
        if ($tenantId) {
            try {
                $user = \App\Models\User::where('tenant_id', $tenantId)->first() ?? \App\Models\User::first();
                if ($user) {
                    PlatformNotification::create([
                        'tenant_id' => $tenantId,
                        'user_id' => $user->id,
                        'type' => 'integration.dead_letter',
                        'title' => "Integration Failure [{$jobType}]",
                        'body' => "Job failed after {$attempts} attempts: {$errorMessage}",
                        'data' => [
                            'dead_letter_id' => $deadLetter->id,
                            'connection_id' => $connectionId,
                            'job_type' => $jobType,
                        ],
                    ]);
                }
            } catch (Throwable $e) {
                Log::warning("Could not dispatch platform notification for dead letter: " . $e->getMessage());
            }
        }

        return $deadLetter;
    }

    /**
     * Replay a dead-letter job.
     */
    public function replay(string $deadLetterId): bool
    {
        $deadLetter = IntegrationDeadLetter::findOrFail($deadLetterId);
        $deadLetter->replayed_at = now();
        $deadLetter->save();

        $payload = $deadLetter->payload ?? [];
        $jobType = $deadLetter->job_type;

        match ($jobType) {
            'inbound_sync' => InboundSyncJob::dispatch($payload),
            'outbound_sync' => OutboundIntegrationJob::dispatch($payload),
            'webhook_delivery' => WebhookDeliveryJob::dispatch($payload),
            default => null,
        };

        return true;
    }
}
