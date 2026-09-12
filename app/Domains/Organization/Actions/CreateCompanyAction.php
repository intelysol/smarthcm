<?php

namespace App\Domains\Organization\Actions;

use App\Domains\Organization\DTOs\CreateCompanyData;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Services\CompanyService;
use App\Domains\Shared\Actions\BaseAction;

class CreateCompanyAction extends BaseAction
{
    public function __construct(
        private readonly CompanyService $service,
    ) {
    }

    public function execute(CreateCompanyData $data): Company
    {
        return $this->service->create($data);
    }
}
