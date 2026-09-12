<?php

namespace App\Domains\SelfService\Http\Controllers;

use App\Domains\SelfService\Models\HrServiceDefinition;
use App\Domains\SelfService\Services\KnowledgeBaseService;
use App\Domains\SelfService\Services\ServiceCatalogService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceCatalogController extends Controller
{
    public function __construct(
        protected ServiceCatalogService $catalogService,
        protected KnowledgeBaseService $knowledgeService
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? 'default';
        $categories = $this->catalogService->getCategories($tenantId);
        $popular = $this->catalogService->getPopularServices($tenantId, 6);

        if ($request->wantsJson()) {
            return response()->json([
                'categories' => $categories,
                'popular_services' => $popular,
            ]);
        }

        return view('self-service.catalog.index', compact('categories', 'popular'));
    }

    public function show(Request $request, HrServiceDefinition $service): View|JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? 'default';
        $serviceData = $this->catalogService->getServiceWithForm($service);
        $deflections = $this->knowledgeService->getDeflectionSuggestions($tenantId, $service->id);

        if ($request->wantsJson()) {
            return response()->json(array_merge($serviceData, ['deflection_articles' => $deflections]));
        }

        return view('self-service.requests.create', array_merge($serviceData, ['deflections' => $deflections]));
    }
}
