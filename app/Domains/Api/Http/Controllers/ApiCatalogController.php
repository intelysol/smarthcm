<?php

declare(strict_types=1);

namespace App\Domains\Api\Http\Controllers;

use App\Domains\Api\Models\ApiEndpoint;
use App\Domains\Api\Models\ApiProduct;
use App\Domains\Api\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiCatalogController
{
    public function catalog(Request $request): JsonResponse
    {
        $products = ApiProduct::with('endpoints')
            ->where('status', 'published')
            ->get();

        return ApiResponse::success($products);
    }

    public function openApiSpec(Request $request): JsonResponse
    {
        $endpoints = ApiEndpoint::where('status', 'published')->get();

        $paths = [];
        foreach ($endpoints as $endpoint) {
            $path = '/' . ltrim($endpoint->path, '/');
            $method = strtolower($endpoint->method);

            $paths[$path][$method] = [
                'summary' => $endpoint->resource . ' operation',
                'operationId' => $method . '_' . str_replace(['/', '{', '}'], ['_', '', ''], $endpoint->path),
                'tags' => [$endpoint->resource],
                'responses' => [
                    '200' => ['description' => 'Successful operation'],
                    '401' => ['description' => 'Unauthorized - Invalid API Key'],
                    '403' => ['description' => 'Forbidden - Insufficient Scopes'],
                    '429' => ['description' => 'Rate limit exceeded'],
                ],
                'security' => [
                    ['ApiKeyAuth' => []],
                    ['BearerAuth' => []],
                ],
            ];
        }

        $spec = [
            'openapi' => '3.0.3',
            'info' => [
                'title' => 'SmartHCM Enterprise API Gateway',
                'description' => 'Authoritative REST & Webhook APIs for Enterprise Human Capital Management.',
                'version' => '1.0.0',
            ],
            'servers' => [
                ['url' => url('/api/v1'), 'description' => 'Production API Gateway'],
            ],
            'paths' => $paths,
            'components' => [
                'securitySchemes' => [
                    'ApiKeyAuth' => [
                        'type' => 'apiKey',
                        'in' => 'header',
                        'name' => 'X-API-Key',
                    ],
                    'BearerAuth' => [
                        'type' => 'http',
                        'scheme' => 'bearer',
                    ],
                ],
            ],
        ];

        return response()->json($spec);
    }

    public function health(): JsonResponse
    {
        return ApiResponse::success([
            'status' => 'operational',
            'gateway' => 'SmartHCM API Management v1.0',
            'time' => now()->toIso8601String(),
        ]);
    }
}
