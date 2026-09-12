<?php

namespace App\Domains\Organization\Policies;

use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Policies\BasePolicy;
use App\Models\User;

class CompanyPolicy extends BasePolicy
{
    public function create(User $user): bool
    {
        return $user->tenant_id !== null;
    }

    public function view(User $user, Company $company): bool
    {
        return $this->belongsToSameTenant($user, $company);
    }
}
