<?php

declare(strict_types=1);

namespace App\Domains\Shared\Http\Controllers;

use App\Domains\Shared\Enums\WorkspaceType;
use App\Domains\Shared\Services\NavigationRegistry;
use App\Domains\Shared\Services\WorkspaceManager;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkspaceSwitcherController extends Controller
{
    public function __construct(
        protected WorkspaceManager $workspaceManager,
        protected NavigationRegistry $navigationRegistry
    ) {}

    /**
     * Switch active user workspace.
     */
    public function switch(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'workspace' => ['required', 'string'],
        ]);

        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $targetWorkspace = WorkspaceType::tryFrom($validated['workspace']);
        if (!$targetWorkspace) {
            return back()->with('error', 'Invalid workspace specified.');
        }

        $success = $this->workspaceManager->switchWorkspace($user, $targetWorkspace);

        if (!$success) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'UNAUTHORIZED_WORKSPACE',
                        'message' => 'You do not have permission to switch to this workspace.',
                    ],
                ], 403);
            }
            abort(403, 'You do not have permission to access the ' . $targetWorkspace->label() . ' workspace.');
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'active_workspace' => $targetWorkspace->value,
                'label' => $targetWorkspace->label(),
                'redirect_url' => route($targetWorkspace->dashboardRoute()),
            ]);
        }

        return redirect()->route($targetWorkspace->dashboardRoute())->with('status', 'Switched to ' . $targetWorkspace->label());
    }

    /**
     * Get active and allowed workspaces payload for frontend shells.
     */
    public function status(): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['authenticated' => false], 401);
        }

        $allowed = $this->workspaceManager->resolveAllowedWorkspaces($user);
        $active = $this->workspaceManager->getActiveWorkspace($user);

        return response()->json([
            'authenticated' => true,
            'active_workspace' => [
                'type' => $active->value,
                'label' => $active->label(),
                'icon' => $active->icon(),
                'route_prefix' => $active->routePrefix(),
            ],
            'allowed_workspaces' => array_map(fn (WorkspaceType $w) => [
                'type' => $w->value,
                'label' => $w->label(),
                'description' => $w->description(),
                'icon' => $w->icon(),
                'dashboard_route' => route($w->dashboardRoute()),
            ], $allowed),
            'navigation' => $this->navigationRegistry->getNavigationFor($active, $user),
        ]);
    }
}
