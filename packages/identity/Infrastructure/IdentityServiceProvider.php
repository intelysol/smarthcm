<?php

declare(strict_types=1);

namespace Flow\Identity\Infrastructure;

use Flow\Identity\Application\Services\IdentityService;
use Illuminate\Support\ServiceProvider;

final class IdentityServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(IdentityService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/migrations');
        $this->loadRoutesFrom(__DIR__.'/../Routes/api.php');
    }
}
