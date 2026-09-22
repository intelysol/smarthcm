<?php

declare(strict_types=1);

namespace Flow\Packages\Integrations\Domain\DTOs;

final class InboundEvent
{
    public function __construct(
        public readonly string $eventId,
        public readonly string $eventType,
        public readonly string $source,
        public readonly array $payload,
        public readonly ?string $idempotencyKey = null,
        public readonly array $headers = [],
        public readonly ?string $timestamp = null,
    ) {}

    public function toArray(): array
    {
        return [
            'event_id' => $this->eventId,
            'event_type' => $this->eventType,
            'source' => $this->source,
            'payload' => $this->payload,
            'idempotency_key' => $this->idempotencyKey,
            'headers' => $this->headers,
            'timestamp' => $this->timestamp ?? now()->toIso8601String(),
        ];
    }
}
