<?php

declare(strict_types=1);

namespace App\Domains\Platform\Http\Controllers;

use App\Domains\Shared\Enums\WorkspaceType;
use App\Domains\Shared\Services\WorkspaceManager;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginWebController extends Controller
{
    public function __construct(
        protected ?WorkspaceManager $workspaceManager = null
    ) {
        $this->workspaceManager = $workspaceManager ?? app(WorkspaceManager::class);
    }

    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            /** @var User $user */
            $user = Auth::user();
            return redirect($this->resolveRedirectDestination($user));
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            /** @var User $user */
            $user = Auth::user();

            // Enforce account status check (SUSPENDED or DISABLED users cannot sign in)
            if (in_array(strtolower($user->status ?? 'active'), ['disabled', 'suspended', 'inactive'], true)) {
                Auth::logout();
                $request->session()->invalidate();

                throw ValidationException::withMessages([
                    'email' => 'Your account has been deactivated. Please contact your organization administrator.',
                ]);
            }

            $request->session()->regenerate();

            // Set tenant session if user has a tenant
            if ($user->tenant_id) {
                $request->session()->put('tenant_uuid', $user->tenant_id);
            }

            // Record last login timestamp
            $user->forceFill(['last_login_at' => now()])->saveQuietly();

            $targetUrl = $this->resolveRedirectDestination($user);

            return redirect($targetUrl);
        }

        throw ValidationException::withMessages([
            'email' => __('auth.failed', [], 'These credentials do not match our records.'),
        ]);
    }

    /**
     * Resolve destination workspace based on user role & privileges.
     */
    public function resolveRedirectDestination(User $user): string
    {
        if ($user->is_platform_admin) {
            return '/platform';
        }

        $allowed = $this->workspaceManager->resolveAllowedWorkspaces($user);
        $primary = $allowed[0] ?? WorkspaceType::EMPLOYEE;

        // Sync session active workspace
        session([WorkspaceManager::SESSION_KEY => $primary->value]);

        return match ($primary) {
            WorkspaceType::PLATFORM_ADMIN => '/platform',
            WorkspaceType::TENANT_ADMIN => '/admin/dashboard',
            WorkspaceType::HR_ADMIN => '/hr/dashboard',
            WorkspaceType::MANAGER => '/manager/workbench',
            WorkspaceType::OPERATIONS => '/operations/dashboard',
            WorkspaceType::EXECUTIVE => '/executive/overview',
            WorkspaceType::EMPLOYEE => '/portal',
            default => '/portal',
        };
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('status', 'You have been signed out successfully.');
    }
}
