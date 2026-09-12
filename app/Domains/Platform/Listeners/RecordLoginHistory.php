<?php

namespace App\Domains\Platform\Listeners;

use App\Domains\Platform\Events\UserAuthenticated;
use App\Domains\Platform\Models\LoginHistory;

class RecordLoginHistory
{
    public function handle(UserAuthenticated $event): void
    {
        LoginHistory::query()->create(['tenant_id' => $event->user->tenant_id, 'user_id' => $event->user->id, 'email' => $event->user->email, 'event' => $event->event, 'ip_address' => $event->ipAddress, 'user_agent' => $event->userAgent, 'occurred_at' => now()]);
    }
}
