<?php

namespace App\Domains\Engagement\Policies;

use App\Domains\Engagement\Models\EmployeeSuggestion;
use App\Models\User;

class EmployeeSuggestionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('hcm.engagement.suggestion.view')
            || $user->hasPermissionTo('hcm.engagement.view');
    }

    public function view(User $user, EmployeeSuggestion $suggestion): bool
    {
        return $user->tenant_id === $suggestion->tenant_id;
    }

    public function create(User $user): bool
    {
        return true; // Any authenticated employee can submit suggestions
    }

    public function manage(User $user): bool
    {
        return $user->hasPermissionTo('hcm.engagement.suggestion.manage')
            || $user->hasPermissionTo('hcm.engagement.manage');
    }
}
