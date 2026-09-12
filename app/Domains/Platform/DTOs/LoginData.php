<?php

namespace App\Domains\Platform\DTOs;

final readonly class LoginData
{
    public function __construct(public string $tenant, public string $email, public string $password, public bool $remember) {}

    /** @param array<string, mixed> $payload */
    public static function fromArray(array $payload): self
    {
        return new self($payload['tenant'], $payload['email'], $payload['password'], (bool) ($payload['remember'] ?? false));
    }
}
