<?php

namespace App\Domains\Rules\DTOs;

final readonly class RuleExecutionContext
{
    /** @param array<string, mixed> $data @param array<string, mixed> $variables */
    public function __construct(public string $tenantId, public array $data, public array $variables = [], public ?string $subjectType = null, public ?string $subjectId = null, public ?string $correlationId = null) {}
}
