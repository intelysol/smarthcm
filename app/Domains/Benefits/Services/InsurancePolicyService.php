<?php

namespace App\Domains\Benefits\Services;

use App\Domains\Benefits\Models\BenefitProvider;
use App\Domains\Benefits\Models\InsurancePolicy;

class InsurancePolicyService
{
    public function createPolicy(BenefitProvider $provider, array $data): InsurancePolicy
    {
        return $provider->insurancePolicies()->create(array_merge($data, [
            'tenant_id' => $provider->tenant_id,
            'is_active' => $data['is_active'] ?? true,
        ]));
    }
}
