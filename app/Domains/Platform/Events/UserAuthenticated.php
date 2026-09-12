<?php

namespace App\Domains\Platform\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

class UserAuthenticated
{
    use Dispatchable;

    public function __construct(public readonly User $user, public readonly string $event, public readonly ?string $ipAddress, public readonly ?string $userAgent) {}
}
