<?php

declare(strict_types=1);

namespace Flow\Identity\Application\Services;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

final class IdentityService
{
    public function login(string $tenantIdentifier, string $identifier, string $password, bool $remember, Request $request): User
    {
        $tenant = Tenant::query()->where('id', $tenantIdentifier)->orWhere('slug', $tenantIdentifier)->where('is_active', true)->first();
        $user = $tenant === null ? null : User::query()->where('tenant_id', $tenant->getKey())->where(fn ($query) => $query->where('email', $identifier)->orWhere('username', $identifier))->first();
        $valid = $user !== null && $user->status === 'active' && $user->locked_at === null && Hash::check($password, $user->password);
        $this->recordAttempt($tenant?->getKey(), $user, $identifier, $request, $valid);
        if (! $valid) {
            throw new AuthenticationException('Invalid credentials.');
        }

        Auth::login($user, $remember);
        $request->session()->regenerate();
        $deviceId = $this->recordDevice($user, $request);
        DB::table('user_sessions')->updateOrInsert(['session_id' => $request->session()->getId()], ['id' => (string) Str::uuid(), 'tenant_id' => $user->tenant_id, 'user_id' => $user->getKey(), 'device_id' => $deviceId, 'ip_address' => $request->ip(), 'user_agent' => $request->userAgent(), 'last_active_at' => now(), 'updated_at' => now(), 'created_at' => now()]);
        $user->forceFill(['last_login_at' => now(), 'login_count' => $user->login_count + 1])->save();

        return $user;
    }

    public function logout(Request $request): void
    {
        if ($request->user() !== null) {
            DB::table('user_sessions')->where('session_id', $request->session()->getId())->update(['revoked_at' => now()]);
        }
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    public function changePassword(User $user, string $currentPassword, string $newPassword): void
    {
        if (! Hash::check($currentPassword, $user->password)) {
            throw new AuthenticationException('Current password is invalid.');
        }
        $history = DB::table('password_histories')->where('user_id', $user->getKey())->latest('created_at')->limit(5)->pluck('password');
        if ($history->contains(fn (string $hash): bool => Hash::check($newPassword, $hash))) {
            throw new \DomainException('A recent password cannot be reused.');
        }
        DB::transaction(function () use ($user, $newPassword): void {
            DB::table('password_histories')->insert(['id' => (string) Str::uuid(), 'user_id' => $user->getKey(), 'password' => $user->password, 'created_at' => now()]);
            $user->forceFill(['password' => Hash::make($newPassword), 'last_password_changed_at' => now(), 'must_change_password' => false])->save();
        });
    }

    /** @return array<int, object> */
    public function sessions(User $user): array
    {
        return DB::table('user_sessions')->where('user_id', $user->getKey())->whereNull('revoked_at')->orderByDesc('last_active_at')->get()->all();
    }

    public function revokeSession(User $user, string $sessionId): void
    {
        abort_unless(DB::table('user_sessions')->where('id', $sessionId)->where('user_id', $user->getKey())->whereNull('revoked_at')->update(['revoked_at' => now()]) === 1, 404);
    }

    /** @return list<string> */
    public function enableTotp(User $user): array
    {
        $secret = Str::upper(Str::random(32));
        DB::table('mfa_methods')->updateOrInsert(['user_id' => $user->getKey(), 'type' => 'totp'], ['id' => (string) Str::uuid(), 'secret' => encrypt($secret), 'verified_at' => now(), 'is_primary' => true, 'updated_at' => now(), 'created_at' => now()]);
        $codes = collect(range(1, 8))->map(fn (): string => Str::upper(Str::random(10)))->all();
        DB::table('mfa_recovery_codes')->where('user_id', $user->getKey())->delete();
        DB::table('mfa_recovery_codes')->insert(array_map(fn (string $code): array => ['id' => (string) Str::uuid(), 'user_id' => $user->getKey(), 'code' => Hash::make($code), 'created_at' => now(), 'updated_at' => now()], $codes));
        $user->forceFill(['mfa_enabled' => true])->save();

        return ['secret' => $secret, 'recovery_codes' => $codes];
    }

    private function recordAttempt(?string $tenantId, ?User $user, string $identifier, Request $request, bool $successful): void
    {
        DB::table('login_attempts')->insert(['id' => (string) Str::uuid(), 'tenant_id' => $tenantId, 'user_id' => $user?->getKey(), 'identifier' => $identifier, 'ip_address' => $request->ip(), 'fingerprint' => hash('sha256', (string) $request->userAgent()), 'successful' => $successful, 'failure_reason' => $successful ? null : 'invalid_credentials', 'attempted_at' => now()]);
    }

    private function recordDevice(User $user, Request $request): string
    {
        $fingerprint = hash('sha256', (string) $request->userAgent());
        $device = DB::table('user_devices')->where('user_id', $user->getKey())->where('fingerprint', $fingerprint)->first();
        $id = $device?->id ?? (string) Str::uuid();
        DB::table('user_devices')->updateOrInsert(['user_id' => $user->getKey(), 'fingerprint' => $fingerprint], ['id' => $id, 'tenant_id' => $user->tenant_id, 'name' => Str::limit((string) $request->userAgent(), 160, ''), 'user_agent' => $request->userAgent(), 'last_ip' => $request->ip(), 'last_seen_at' => now(), 'updated_at' => now(), 'created_at' => $device?->created_at ?? now()]);
        return $id;
    }
}
