<?php
namespace App\Domains\Integration\Services;
use App\Domains\Integration\Models\{IntegrationConnection, IntegrationEvent, IntegrationConnector, WebhookSubscription};
use Illuminate\Support\Facades\Http;
class IntegrationService
{
    public function connection(string $tenantId, array $data): IntegrationConnection { return IntegrationConnection::query()->create([...$data, 'tenant_id' => $tenantId]); }
    public function publish(string $tenantId, string $type, array $payload, ?string $subjectType = null, ?string $subjectId = null): IntegrationEvent
    {
        $event = IntegrationEvent::query()->create(['tenant_id' => $tenantId, 'event_type' => $type, 'payload' => $payload, 'subject_type' => $subjectType, 'subject_id' => $subjectId, 'occurred_at' => now()]);
        foreach (WebhookSubscription::query()->where('tenant_id', $tenantId)->where('status', 'active')->get() as $subscription) {
            if ($subscription->event_filters && ! in_array($type, $subscription->event_filters, true)) continue;
            dispatch(function () use ($subscription, $event): void { $signature = hash_hmac('sha256', json_encode($event->payload, JSON_THROW_ON_ERROR), (string) $subscription->secret_reference); Http::withHeaders(['X-FEP-Event' => $event->event_type, 'X-FEP-Signature' => $signature])->post($subscription->url, $event->payload); });
        }
        return $event;
    }
    public function verifyWebhook(string $payload, string $signature, string $secret): bool { return hash_equals(hash_hmac('sha256', $payload, $secret), $signature); }
}
