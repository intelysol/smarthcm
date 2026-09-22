<?php

declare(strict_types=1);

namespace App\Domains\Integration\Jobs;

use App\Domains\Integration\Models\WebhookDelivery;
use App\Domains\Integration\Models\WebhookSubscription;
use App\Domains\Integration\Services\DeadLetterService;
use App\Domains\Integration\Services\WebhookSecurityService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

class WebhookDeliveryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 4;
    public array $backoff = [5, 30, 120, 600];

    public function __construct(
        public array $params
    ) {}

    public function handle(
        WebhookSecurityService $securityService,
        DeadLetterService $deadLetterService
    ): void {
        $subscriptionId = $this->params['subscription_id'] ?? null;
        $eventId = $this->params['event_id'] ?? null;
        $payload = $this->params['payload'] ?? [];

        $subscription = WebhookSubscription::find($subscriptionId);
        if (!$subscription || $subscription->status !== 'active') {
            return;
        }

        $headers = [
            'Content-Type' => 'application/json',
            'User-Agent' => 'SmartHCM-WebhookEngine/1.0',
        ];

        if (!empty($subscription->secret_reference)) {
            $headers = array_merge($headers, $securityService->signOutgoingPayload($payload, (string) $subscription->secret_reference));
        }

        $attemptNumber = $this->attempts();
        $responseCode = null;
        $responseBody = null;

        try {
            $response = Http::timeout(15)
                ->withHeaders($headers)
                ->post($subscription->url, $payload);

            $responseCode = $response->status();
            $responseBody = Str::limit($response->body(), 1000);

            if ($response->successful()) {
                WebhookDelivery::create([
                    'subscription_id' => $subscription->id,
                    'event_id' => $eventId,
                    'status' => 'delivered',
                    'attempt' => $attemptNumber,
                    'response_code' => $responseCode,
                    'response_body' => $responseBody,
                    'delivered_at' => now(),
                ]);
                return;
            }

            // If 4xx (client error) except 429, do not retry
            if ($responseCode >= 400 && $responseCode < 500 && $responseCode !== 429) {
                WebhookDelivery::create([
                    'subscription_id' => $subscription->id,
                    'event_id' => $eventId,
                    'status' => 'failed_non_retryable',
                    'attempt' => $attemptNumber,
                    'response_code' => $responseCode,
                    'response_body' => $responseBody,
                    'delivered_at' => null,
                ]);

                $deadLetterService->recordDeadLetter(
                    $subscription->connection_id,
                    'webhook_delivery',
                    $this->params,
                    "Webhook target returned HTTP {$responseCode} non-retryable error.",
                    $attemptNumber,
                    $subscription->tenant_id
                );
                return;
            }

            // For 5xx or 429, retry
            throw new \RuntimeException("Webhook failed with status {$responseCode}");
        } catch (Throwable $e) {
            $isLastAttempt = ($attemptNumber >= $this->tries);

            WebhookDelivery::create([
                'subscription_id' => $subscription->id,
                'event_id' => $eventId,
                'status' => $isLastAttempt ? 'failed' : 'retrying',
                'attempt' => $attemptNumber,
                'response_code' => $responseCode,
                'response_body' => $responseBody ?? Str::limit($e->getMessage(), 1000),
                'next_retry_at' => $isLastAttempt ? null : now()->addSeconds($this->backoff[min($attemptNumber - 1, count($this->backoff) - 1)]),
            ]);

            if ($isLastAttempt) {
                $deadLetterService->recordDeadLetter(
                    $subscription->connection_id,
                    'webhook_delivery',
                    $this->params,
                    $e->getMessage(),
                    $attemptNumber,
                    $subscription->tenant_id
                );
            } else {
                throw $e;
            }
        }
    }
}
