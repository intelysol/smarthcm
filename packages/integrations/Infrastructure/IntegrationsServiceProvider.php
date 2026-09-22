<?php

declare(strict_types=1);

namespace Flow\Packages\Integrations\Infrastructure;

use Flow\Packages\Integrations\Domain\Contracts\CredentialManagerInterface;
use Flow\Packages\Integrations\Infrastructure\Services\ConnectorRegistry;
use App\Domains\Integration\Services\CredentialManager;
use Illuminate\Support\ServiceProvider;

class IntegrationsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ConnectorRegistry::class, function () {
            return new ConnectorRegistry();
        });

        $this->app->bind(CredentialManagerInterface::class, CredentialManager::class);
    }

    public function boot(): void
    {
        // Integration hub boots up
    }
}
