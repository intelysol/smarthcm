<?php

use App\Providers\AppServiceProvider;
use App\Providers\DomainServiceProvider;
use Flow\Identity\Infrastructure\IdentityServiceProvider;

return [
    AppServiceProvider::class,
    DomainServiceProvider::class,
    IdentityServiceProvider::class,
    Flow\Packages\Integrations\Infrastructure\IntegrationsServiceProvider::class,
    Flow\Packages\Billing\Infrastructure\BillingServiceProvider::class,
];
