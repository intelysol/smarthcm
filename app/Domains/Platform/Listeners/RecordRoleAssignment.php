<?php

namespace App\Domains\Platform\Listeners;

use App\Domains\Platform\Events\RoleAssigned;
use App\Domains\Shared\Services\ActivityLogService;

class RecordRoleAssignment
{
    public function __construct(private readonly ActivityLogService $activityLog) {}

    public function handle(RoleAssigned $event): void
    {
        $this->activityLog->record('role.assigned', $event->role, $event->actor->id, null, ['user_id' => $event->user->id]);
    }
}
