<?php

declare(strict_types=1);

namespace App\Domains\Api\Http\Middleware;

use App\Domains\Api\Models\ApiClient;
use App\Domains\Api\Support\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceApiScopes
{
    public function handle(Request $request, Closure $next, string ...$requiredScopes): Response
    {
        /** @var ApiClient|null $client */
        $client = $request->attributes->get('api_client');

        if (!$client) {
            return ApiResponse::error('UNAUTHENTICATED', 'No authenticated API client found.', [], 401);
        }

        $clientScopes = (array) ($client->scopes ?? []);

        // Super wildcard scope
        if (in_array('*', $clientScopes, true) || in_array('all', $clientScopes, true)) {
            return $next($request);
        }

        foreach ($requiredScopes as $scope) {
            if (!in_array($scope, $clientScopes, true)) {
                // Check wildcard like 'employees:*' matching 'employees:read'
                $prefix = explode(':', $scope)[0] . ':*';
                if (!in_array($prefix, $clientScopes, true)) {
                    return ApiResponse::error(
                        'FORBIDDEN_SCOPE',
                        "Client lacks required scope [{$scope}].",
                        [
                            'required_scopes' => $requiredScopes,
                            'granted_scopes' => $clientScopes,
                        ],
                        403
                    );
                }
            }
        }

        return $next($request);
    }
}
