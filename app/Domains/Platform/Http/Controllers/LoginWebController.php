<?php

declare(strict_types=1);

namespace App\Domains\Platform\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginWebController extends Controller
{
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->intended('/portal');
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
            $request->session()->regenerate();

            // Set tenant session if user has a tenant
            $user = Auth::user();
            if ($user && $user->tenant_id) {
                $request->session()->put('tenant_uuid', $user->tenant_id);
            }

            return redirect()->intended('/portal');
        }

        throw ValidationException::withMessages([
            'email' => __('auth.failed', [], 'These credentials do not match our records.'),
        ]);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('status', 'You have been signed out successfully.');
    }
}
