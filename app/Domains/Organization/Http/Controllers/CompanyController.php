<?php

namespace App\Domains\Organization\Http\Controllers;

use App\Domains\Organization\Actions\CreateCompanyAction;
use App\Domains\Organization\DTOs\CreateCompanyData;
use App\Domains\Organization\Requests\StoreCompanyRequest;
use App\Domains\Organization\Resources\CompanyResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class CompanyController extends Controller
{
    public function store(StoreCompanyRequest $request, CreateCompanyAction $action): JsonResponse
    {
        $user = $request->user();

        abort_if($user === null, 401);

        $company = $action->execute(
            CreateCompanyData::fromArray($request->validated(), $user->id),
        );

        return CompanyResource::make($company)
            ->response()
            ->setStatusCode(201);
    }
}
