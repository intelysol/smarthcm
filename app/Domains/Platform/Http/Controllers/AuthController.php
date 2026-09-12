<?php

namespace App\Domains\Platform\Http\Controllers;

use App\Domains\Platform\Actions\LoginAction;
use App\Domains\Platform\DTOs\LoginData;
use App\Domains\Platform\Requests\LoginRequest;
use App\Domains\Platform\Resources\UserResource;
use App\Domains\Platform\Services\AuthenticationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class AuthController
{
    public function __construct(private readonly LoginAction $login, private readonly AuthenticationService $authentication) {}

    public function login(LoginRequest $request): JsonResponse
    {
        return UserResource::make($this->login->execute(LoginData::fromArray($request->validated()), $request)->load(['profile', 'preference', 'roles.permissions']))->response();
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authentication->logout($request);

        return response()->json(['message' => 'Logged out.']);
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $payload = $request->validate(['email' => ['required', 'email']]);
        $status = Password::sendResetLink($payload);

        return response()->json(['message' => __($status)], $status === Password::RESET_LINK_SENT ? 200 : 422);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $payload = $request->validate(['token' => ['required'], 'email' => ['required', 'email'], 'password' => ['required', 'confirmed', 'min:12']]);
        $status = Password::reset($payload, fn ($user, $password) => $user->forceFill(['password' => $password])->save());

        return response()->json(['message' => __($status)], $status === Password::PASSWORD_RESET ? 200 : 422);
    }

    public function me(Request $request): UserResource
    {
        return UserResource::make($request->user()->load(['profile', 'preference', 'roles.permissions']));
    }

    public function changePassword(Request $request): JsonResponse
    {
        $payload = $request->validate(['current_password' => ['required', 'string'], 'password' => ['required', 'string', 'confirmed', 'min:12']]);
        $this->authentication->changePassword($request->user(), $payload['current_password'], $payload['password']);

        return response()->json(['message' => 'Password changed.']);
    }
}
