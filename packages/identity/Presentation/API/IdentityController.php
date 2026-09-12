<?php

declare(strict_types=1);

namespace Flow\Identity\Presentation\API;

use Flow\Identity\Application\Services\IdentityService;
use Flow\Identity\Presentation\Requests\ChangePasswordRequest;
use Flow\Identity\Presentation\Requests\LoginRequest;
use Flow\Identity\Presentation\Requests\UpdateProfileRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

final class IdentityController
{
    public function __construct(private readonly IdentityService $identity) {}

    public function login(LoginRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = $this->identity->login($data['tenant'], $data['identifier'], $data['password'], (bool) ($data['remember'] ?? false), $request);
        return response()->json(['data' => ['id' => $user->uuid, 'email' => $user->email, 'must_change_password' => $user->must_change_password, 'mfa_required' => $user->mfa_enabled]]);
    }

    public function logout(Request $request): JsonResponse { $this->identity->logout($request); return response()->json(['message' => 'Logged out.']); }
    public function forgotPassword(Request $request): JsonResponse { $status = Password::sendResetLink($request->validate(['email' => ['required', 'email']])); return response()->json(['message' => __($status)], $status === Password::RESET_LINK_SENT ? 200 : 422); }
    public function resetPassword(Request $request): JsonResponse { $data = $request->validate(['token' => ['required'], 'email' => ['required', 'email'], 'password' => ['required', 'confirmed', 'min:12']]); $status = Password::reset($data, fn ($user, $password) => $user->forceFill(['password' => $password, 'last_password_changed_at' => now()])->save()); return response()->json(['message' => __($status)], $status === Password::PASSWORD_RESET ? 200 : 422); }
    public function verifyEmail(Request $request): JsonResponse { $request->user()->forceFill(['email_verified_at' => now()])->save(); return response()->json(['message' => 'Email verified.']); }
    public function profile(Request $request): JsonResponse { return response()->json(['data' => $request->user()->load(['profile', 'preference'])]); }
    public function updateProfile(UpdateProfileRequest $request): JsonResponse { $request->user()->fill($request->validated())->save(); return $this->profile($request); }
    public function changePassword(ChangePasswordRequest $request): JsonResponse { $data = $request->validated(); $this->identity->changePassword($request->user(), $data['current_password'], $data['password']); return response()->json(['message' => 'Password changed.']); }
    public function sessions(Request $request): JsonResponse { return response()->json(['data' => $this->identity->sessions($request->user())]); }
    public function revokeSession(Request $request, string $id): JsonResponse { $this->identity->revokeSession($request->user(), $id); return response()->json([], 204); }
    public function enableMfa(Request $request): JsonResponse { return response()->json(['data' => $this->identity->enableTotp($request->user())], 201); }
    public function disableMfa(Request $request): JsonResponse { $request->user()->forceFill(['mfa_enabled' => false])->save(); return response()->json(['message' => 'MFA disabled.']); }
}
