<?php

declare(strict_types=1);

namespace Flow\Packages\Integrations\Domain\DTOs;

final class SyncContext
{
    public function __construct(
        public readonly string $tenantId,
        public readonly string $connectionId,
        public readonly ?string $syncRunId = null,
        public readonly string $direction = 'inbound',
        public readonly string $entityType = 'employee',
        public readonly array $parameters = [],
        public readonly ?string $cursor = null,
        public readonly int $batchSize = 100,
    ) {}
}
