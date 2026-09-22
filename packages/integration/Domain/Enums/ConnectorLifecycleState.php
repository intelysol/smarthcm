<?php

declare(strict_types=1);

namespace Flow\Packages\Integrations\Domain\Enums;

enum ConnectorLifecycleState: string
{
    case Draft = 'draft';
    case Configured = 'configured';
    case Authenticated = 'authenticated';
    case Active = 'active';
    case Paused = 'paused';
    case Disabled = 'disabled';
    case Retired = 'retired';

    public function isOperational(): bool
    {
        return $this === self::Active;
    }

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Configured => 'Configured',
            self::Authenticated => 'Authenticated',
            self::Active => 'Active',
            self::Paused => 'Paused',
            self::Disabled => 'Disabled',
            self::Retired => 'Retired',
        };
    }
}
