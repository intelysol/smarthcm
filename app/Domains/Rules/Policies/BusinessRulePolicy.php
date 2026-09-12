<?php

namespace App\Domains\Rules\Policies;

use App\Domains\Rules\Models\BusinessRule;
use App\Models\User;

class BusinessRulePolicy
{
    public function viewAny(User $user): bool { return $user->hasPermission('rules.view'); }
    public function create(User $user): bool { return $user->hasPermission('rules.manage'); }
    public function update(User $user, BusinessRule $rule): bool { return (string) $rule->tenant_id === (string) $user->tenant_id && $user->hasPermission('rules.manage'); }
    public function execute(User $user, BusinessRule $rule): bool { return (string) $rule->tenant_id === (string) $user->tenant_id && $user->hasPermission('rules.execute'); }
}
