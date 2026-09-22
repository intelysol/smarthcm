<?php

declare(strict_types=1);

namespace Flow\Packages\Integrations\Domain\Contracts;

interface CredentialManagerInterface
{
    /**
     * Resolve decrypted credentials for a given connection ID.
     */
    public function resolveCredentials(string $connectionId): array;

    /**
     * Store and encrypt credentials for a given connection ID.
     */
    public function storeCredentials(string $connectionId, array $credentials): bool;

    /**
     * Mask sensitive credential fields (tokens, api keys, client secrets, passwords).
     */
    public function maskCredentials(array $credentials): array;

    /**
     * Refresh OAuth2 access token if expired or expiring soon.
     */
    public function refreshOAuthToken(string $connectionId): array;
}
