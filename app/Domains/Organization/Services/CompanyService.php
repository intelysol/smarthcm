<?php

namespace App\Domains\Organization\Services;

use App\Domains\Organization\DTOs\CreateCompanyData;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Repositories\CompanyRepositoryInterface;
use App\Domains\Shared\Services\BaseService;
use Illuminate\Validation\ValidationException;

class CompanyService extends BaseService
{
    public function __construct(
        private readonly CompanyRepositoryInterface $companies,
    ) {
    }

    public function create(CreateCompanyData $data): Company
    {
        if ($this->companies->existsForTenantByName($data->tenantId, $data->name)) {
            throw ValidationException::withMessages([
                'name' => 'A company with this name already exists for the tenant.',
            ]);
        }

        return $this->companies->create([
            'tenant_id' => $data->tenantId,
            'name' => $data->name,
            'legal_name' => $data->legalName,
            'registration_number' => $data->registrationNumber,
            'tax_number' => $data->taxNumber,
            'email' => $data->email,
            'phone' => $data->phone,
            'website' => $data->website,
            'timezone' => $data->timezone,
            'currency' => $data->currency,
            'is_active' => true,
            'created_by' => $data->actorId,
            'updated_by' => $data->actorId,
        ]);
    }
}
