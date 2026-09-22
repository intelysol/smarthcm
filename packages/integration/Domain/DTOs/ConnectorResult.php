<?php

declare(strict_types=1);

namespace Flow\Packages\Integrations\Domain\DTOs;

final class ConnectorResult
{
    public function __construct(
        public readonly bool $success,
        public readonly int $statusCode,
        public readonly mixed $data = null,
        public readonly ?string $errorMessage = null,
        public readonly array $metadata = [],
        public readonly array $rateLimitHeaders = [],
        public readonly ?string $cursor = null,
    ) {}

    public static function ok(mixed $data = null, int $statusCode = 200, array $metadata = [], ?string $cursor = null): self
    {
        return new self(
            success: true,
            statusCode: $statusCode,
            data: $data,
            errorMessage: null,
            metadata: $metadata,
            cursor: $cursor,
        );
    }

    public static function failure(string $errorMessage, int $statusCode = 500, mixed $data = null, array $metadata = []): self
    {
        return new self(
            success: false,
            statusCode: $statusCode,
            data: $data,
            errorMessage: $errorMessage,
            metadata: $metadata,
        );
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'status_code' => $this->statusCode,
            'data' => $this->data,
            'error_message' => $this->errorMessage,
            'metadata' => $this->metadata,
            'cursor' => $this->cursor,
        ];
    }
}
