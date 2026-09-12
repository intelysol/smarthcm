<?php
namespace App\Domains\Search\Http\Controllers;
use App\Domains\Platform\Contracts\TenantContext;
use App\Domains\Search\Models\{SavedSearch, SearchQuery};
use App\Domains\Search\Services\SearchService;
use Illuminate\Http\{JsonResponse, Request};
class SearchController
{
    public function __construct(private readonly SearchService $search, private readonly TenantContext $tenant) {}
    public function search(Request $request): JsonResponse { $this->allow($request, 'search.view'); $data = $request->validate(['q' => ['nullable', 'string', 'max:300'], 'filters' => ['nullable', 'array']]); return response()->json(['data' => $this->search->search($this->tenant->id(), $request->user(), $data['q'] ?? '', $data['filters'] ?? [])]); }
    public function suggest(Request $request): JsonResponse { $this->allow($request, 'search.view'); return response()->json(['data' => $this->search->suggest($this->tenant->id(), (string) $request->query('q', ''))]); }
    public function save(Request $request): JsonResponse { $this->allow($request, 'search.manage'); $data = $request->validate(['name' => ['required', 'string', 'max:120'], 'query' => ['nullable', 'string', 'max:300'], 'filters' => ['nullable', 'array'], 'is_pinned' => ['sometimes', 'boolean'], 'is_shared' => ['sometimes', 'boolean'], 'schedule' => ['nullable', 'string', 'max:80']]); return response()->json(['data' => SavedSearch::query()->create([...$data, 'tenant_id' => $this->tenant->id(), 'user_id' => $request->user()->id])], 201); }
    public function history(Request $request): JsonResponse { $this->allow($request, 'search.view'); return response()->json(['data' => SearchQuery::query()->where('tenant_id', $this->tenant->id())->where('user_id', $request->user()->id)->latest()->limit(50)->get()]); }
    private function allow(Request $request, string $permission): void { $request->user()->hasPermission($permission) || abort(403); }
}
