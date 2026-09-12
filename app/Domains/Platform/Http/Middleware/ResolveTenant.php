<?php

namespace App\Domains\Platform\Http\Middleware;

use App\Domains\Platform\Contracts\TenantContext;
use App\Domains\Shared\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenant
{
    public function __construct(private readonly TenantContext $context) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $identifier = $request->session()->get('tenant_uuid') ?? $request->header('X-Tenant') ?? $this->subdomain($request);
        abort_if($user === null, Response::HTTP_UNAUTHORIZED);

        $tenant = Tenant::query()->where(fn ($query) => $query->where('id', $identifier)->orWhere('uuid', $identifier)->orWhere('slug', $identifier))->first();
        abort_if($tenant === null || ! $tenant->isAccessible(), Response::HTTP_FORBIDDEN, 'Tenant is unavailable.');
        abort_unless($user->canAccessTenant($tenant), Response::HTTP_FORBIDDEN, 'Tenant access denied.');

        $this->context->set($tenant);
        try {
            return $next($request);
        } finally {
            $this->context->clear();
        }
    }

    private function subdomain(Request $request): ?string
    {
        $parts = explode('.', $request->getHost());

        return count($parts) > 2 ? $parts[0] : null;
    }
}
