<?php

namespace App\Domains\Platform\Services;

use App\Domains\Platform\DTOs\LoginData;
use App\Domains\Platform\Events\UserAuthenticated;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthenticationService
{
    public function login(LoginData $data, Request $request): User
    {
        $tenant = Tenant::query()->where(fn ($query) => $query->where('id', $data->tenant)->orWhere('uuid', $data->tenant)->orWhere('slug', $data->tenant))->first();
        if ($tenant === null || ! $tenant->isAccessible() || ! Auth::attempt(['email' => $data->email, 'password' => $data->password, 'status' => 'active'], $data->remember)) {
            throw new AuthenticationException('Invalid credentials.');
        }

        $request->session()->regenerate();
        $user = $request->user();
        if (! $user->canAccessTenant($tenant)) {
            Auth::logout();
            throw new AuthenticationException('Invalid credentials.');
        }
        $request->session()->put('tenant_uuid', $tenant->uuid ?? $tenant->id);
        UserAuthenticated::dispatch($user, 'login', $request->ip(), $request->userAgent());

        return $user;
    }

    public function logout(Request $request): void
    {
        $user = $request->user();
        if ($user !== null) {
            UserAuthenticated::dispatch($user, 'logout', $request->ip(), $request->userAgent());
        }
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    public function changePassword(User $user, string $currentPassword, string $password): void
    {
        if (! Hash::check($currentPassword, $user->password)) {
            throw new AuthenticationException('Current password is invalid.');
        }
        $user->forceFill(['password' => Hash::make($password)])->save();
    }
}
