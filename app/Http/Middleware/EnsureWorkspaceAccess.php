<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Domains\Shared\Enums\WorkspaceType;
use App\Domains\Shared\Services\WorkspaceManager;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureWorkspaceAccess
{
    public function __construct(
        protected WorkspaceManager $workspaceManager
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ?string $workspace = null): Response
    {
        $user = Auth::user();

        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'UNAUTHENTICATED',
                        'message' => 'Authentication is required to access this workspace.',
                    ],
                ], 401);
            }
            return redirect()->guest('/login');
        }

        // Infer workspace type from parameter or route URI
        $targetType = $this->resolveTargetWorkspace($request, $workspace);

        if ($targetType !== null) {
            if (!$this->workspaceManager->canAccess($user, $targetType)) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'error' => [
                            'code' => 'FORBIDDEN',
                            'message' => "Access denied. You do not have permission to access the '{$targetType->label()}' workspace.",
                        ],
                    ], 403);
                }

                $activeWorkspace = $this->workspaceManager->getActiveWorkspace($user);
                return response()->view('errors.403', [
                    'message' => "Access Restricted: You are not authorized to access the {$targetType->label()} workspace.",
                    'targetWorkspace' => $targetType,
                    'fallbackWorkspace' => $activeWorkspace,
                ], 403);
            }

            // Sync active workspace to session
            session([WorkspaceManager::SESSION_KEY => $targetType->value]);
        }

        return $next($request);
    }

    /**
     * Resolve target WorkspaceType from explicit argument or route path.
     */
    protected function resolveTargetWorkspace(Request $request, ?string $explicit): ?WorkspaceType
    {
        if ($explicit) {
            return WorkspaceType::tryFrom($explicit);
        }

        $path = trim($request->path(), '/');

        if (str_starts_with($path, 'platform')) {
            return WorkspaceType::PLATFORM_ADMIN;
        }

        if (str_starts_with($path, 'admin')) {
            return WorkspaceType::TENANT_ADMIN;
        }

        if (str_starts_with($path, 'hr')) {
            return WorkspaceType::HR_ADMIN;
        }

        if (str_starts_with($path, 'manager') || str_starts_with($path, 'portal/manager')) {
            return WorkspaceType::MANAGER;
        }

        if (str_starts_with($path, 'executive')) {
            return WorkspaceType::EXECUTIVE;
        }

        if (str_starts_with($path, 'operations')) {
            return WorkspaceType::OPERATIONS;
        }

        if (str_starts_with($path, 'employee') || str_starts_with($path, 'portal')) {
            return WorkspaceType::EMPLOYEE;
        }

        return null;
    }
}
