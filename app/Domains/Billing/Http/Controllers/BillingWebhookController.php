<?php

declare(strict_types=1);

namespace App\Domains\Billing\Http\Controllers;

use Flow\Packages\Billing\Domain\Enums\InvoiceStatus;
use Flow\Packages\Billing\Domain\Enums\PaymentStatus;
use Flow\Packages\Billing\Domain\Models\BillingEvent;
use Flow\Packages\Billing\Domain\Models\BillingInvoice;
use Flow\Packages\Billing\Domain\Models\BillingPayment;
use Flow\Packages\Billing\Domain\Models\BillingProviderAccount;
use Flow\Packages\Billing\Services\PaymentManager;
use Flow\Packages\Billing\Services\SubscriptionLifecycleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BillingWebhookController extends Controller
{
    public function __construct(
        protected PaymentManager $paymentManager,
        protected SubscriptionLifecycleService $lifecycleService
    ) {}

    public function handle(string $providerCode, Request $request): JsonResponse
    {
        $rawPayload = (string) $request->getContent();
        $signature = (string) ($request->header('Stripe-Signature') ?? $request->header('X-Webhook-Signature') ?? '');

        // 1. Provider validation
        $provider = $this->paymentManager->getProvider($providerCode);
        $providerAccount = BillingProviderAccount::where('provider_code', $providerCode)->first();
        $secret = $providerAccount->config['webhook_secret'] ?? config("services.billing.{$providerCode}.webhook_secret", 'whsec_test_secret');

        // 2. Verify Signature
        if ($signature !== '' && ! $provider->verifyWebhookSignature($rawPayload, $signature, $secret)) {
            return response()->json(['error' => 'Invalid webhook signature'], 400);
        }

        $payload = $request->json()->all();
        $eventId = (string) ($payload['id'] ?? 'evt_' . Str::random(16));
        $eventType = (string) ($payload['type'] ?? 'unknown');

        // 3. Webhook Idempotency Check
        $alreadyProcessed = BillingEvent::where('event_type', 'WebhookReceived:' . $eventId)->exists();
        if ($alreadyProcessed) {
            return response()->json(['status' => 'ignored_duplicate', 'event_id' => $eventId]);
        }

        DB::transaction(function () use ($providerCode, $eventId, $eventType, $payload) {
            BillingEvent::create([
                'id' => (string) Str::uuid(),
                'tenant_id' => $payload['data']['object']['metadata']['tenant_id'] ?? null,
                'event_type' => 'WebhookReceived:' . $eventId,
                'payload' => ['provider' => $providerCode, 'type' => $eventType, 'data' => $payload],
                'created_at' => now(),
            ]);

            // Dispatch event handling
            if (in_array($eventType, ['payment_intent.succeeded', 'charge.succeeded'])) {
                $object = $payload['data']['object'] ?? [];
                $invoiceId = $object['metadata']['invoice_id'] ?? null;
                $txnId = $object['id'] ?? 'txn_' . Str::random(12);
                $amount = (float) (($object['amount_received'] ?? $object['amount'] ?? 0) / 100);

                if ($invoiceId) {
                    $invoice = BillingInvoice::find($invoiceId);
                    if ($invoice && ! $invoice->isPaid()) {
                        $this->paymentManager->processPayment($invoice, $amount > 0 ? $amount : (float) $invoice->balance_due, $providerCode, [
                            'type' => 'webhook_settlement',
                            'provider_transaction_id' => $txnId,
                        ]);
                    }
                }
            } elseif ($eventType === 'customer.subscription.deleted') {
                $subId = $payload['data']['object']['metadata']['subscription_id'] ?? null;
                if ($subId) {
                    $sub = \Flow\Packages\Billing\Domain\Models\BillingSubscription::find($subId);
                    if ($sub) {
                        $this->lifecycleService->cancel($sub, 'Webhook cancellation from provider');
                    }
                }
            }
        });

        return response()->json(['status' => 'success', 'event_id' => $eventId]);
    }
}
