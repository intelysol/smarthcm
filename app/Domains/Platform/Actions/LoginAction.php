<?php

namespace App\Domains\Platform\Actions;

use App\Domains\Platform\DTOs\LoginData;
use App\Domains\Platform\Services\AuthenticationService;
use App\Models\User;
use Illuminate\Http\Request;

class LoginAction
{
    public function __construct(private readonly AuthenticationService $authentication) {}

    public function execute(LoginData $data, Request $request): User
    {
        return $this->authentication->login($data, $request);
    }
}
