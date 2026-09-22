<?php

declare(strict_types=1);

namespace App\Domains\Platform\Http\Middleware;

use App\Domains\Billing\Support\CommercialFacade as Billing;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceCommercialEntitlement
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @param string $entitlementKey
     * @return Response
     */
    public function handle(Request $request, Closure $next, string $entitlementKey): Response
    {
        $tenantId = (string) (
            $request->header('X-Tenant-ID')
            ?? $request->query('tenant_id')
            ?? $request->user()?->tenant_id
            ?? session('tenant_uuid')
            ?? ''
        );

        if ($tenantId === '') {
            return $next($request);
        }

        $isEntitled = Billing::entitled($tenantId, $entitlementKey);

        if (! $isEntitled) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'COMMERCIAL_ENTITLEMENT_REQUIRED',
                        'message' => "Tenant does not have the required commercial entitlement '{$entitlementKey}'. Please upgrade your subscription plan.",
                        'details' => [
                            'required_entitlement' => $entitlementKey,
                            'upgrade_url' => url('/portal/billing'),
                        ],
                    ],
                    'request_id' => $request->attributes->get('request_id'),
                ], 403);
            }

            return redirect('/portal/billing')
                ->with('warning', "Access to this feature requires the '{$entitlementKey}' entitlement. Please upgrade your commercial plan.");
        }

        return $next($request);
    }
}
