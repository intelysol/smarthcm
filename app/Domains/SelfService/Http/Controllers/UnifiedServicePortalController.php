<?php

namespace App\Domains\SelfService\Http\Controllers;

use App\Domains\Employee\Models\Employee;
use App\Domains\SelfService\Models\HrKnowledgeArticle;
use App\Domains\SelfService\Models\HrServiceCategory;
use App\Domains\SelfService\Models\HrServiceDefinition;
use App\Domains\SelfService\Models\HrServiceRequest;
use App\Domains\SelfService\Services\UnifiedServiceSearchAndAiService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UnifiedServicePortalController extends Controller
{
    public function __construct(
        protected UnifiedServiceSearchAndAiService $searchService
    ) {}

    /**
     * Single-box intent search across Knowledge Base and Service Catalog.
     */
    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => 'required|string|min:2|max:255',
        ]);

        $tenantId = $request->user()?->tenant_id ?? $request->header('X-Tenant-ID') ?? 'default';
        $results = $this->searchService->searchIntent($tenantId, $validated['q']);

        return response()->json($results);
    }

    /**
     * Unified employee service portal dashboard.
     */
    public function portalDashboard(Request $request): JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? $request->header('X-Tenant-ID') ?? 'default';
        $user = $request->user();
        $employee = Employee::where('tenant_id', $tenantId)->where('user_id', $user?->id)->first();

        $activeRequests = $employee ? HrServiceRequest::where('tenant_id', $tenantId)
            ->where('employee_id', $employee->id)
            ->whereNotIn('status', ['closed', 'cancelled'])
            ->with(['service.category'])
            ->latest()
            ->take(5)
            ->get() : [];

        $popularServices = HrServiceDefinition::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->where('is_popular', true)
            ->with('category')
            ->take(6)
            ->get();

        $categories = HrServiceCategory::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderBy('display_order', 'asc')
            ->get();

        $featuredArticles = HrKnowledgeArticle::where('tenant_id', $tenantId)
            ->where('status', 'published')
            ->latest('views_count')
            ->take(5)
            ->get();

        return response()->json([
            'employee' => $employee ? [
                'id' => $employee->id,
                'name' => "{$employee->first_name} {$employee->last_name}",
                'employee_number' => $employee->employee_number,
                'department' => $employee->department?->name,
            ] : null,
            'my_active_requests' => $activeRequests,
            'popular_services' => $popularServices,
            'categories' => $categories,
            'featured_articles' => $featuredArticles,
        ]);
    }
}
