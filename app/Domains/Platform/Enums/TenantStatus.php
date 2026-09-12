<?php

namespace App\Domains\Platform\Enums;

enum TenantStatus: string
{
    case Pending = 'pending';
    case Trial = 'trial';
    case Active = 'active';
    case Suspended = 'suspended';
    case Locked = 'locked';
    case Cancelled = 'cancelled';
    case Archived = 'archived';

    public function isAccessible(): bool
    {
        return $this === self::Active || $this === self::Trial;
    }

    /** @return list<self> */
    public function transitions(): array
    {
        return match ($this) {
            self::Pending => [self::Trial, self::Active, self::Cancelled],
            self::Trial => [self::Active, self::Suspended, self::Cancelled],
            self::Active => [self::Suspended, self::Locked, self::Cancelled],
            self::Suspended => [self::Active, self::Locked, self::Cancelled],
            self::Locked => [self::Active, self::Suspended, self::Cancelled],
            self::Cancelled => [self::Archived],
            self::Archived => [],
        };
    }
}
