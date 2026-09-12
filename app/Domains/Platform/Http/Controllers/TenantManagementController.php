<?php

namespace App\Domains\Platform\Http\Controllers;

use App\Domains\Platform\Enums\TenantStatus;
use App\Domains\Platform\Requests\StoreTenantRequest;
use App\Domains\Platform\Resources\TenantResource;
use App\Domains\Platform\Services\TenantLifecycleService;
use App\Domains\Platform\Services\TenantProvisioningService;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TenantManagementController
{
    public function __construct(private readonly TenantProvisioningService $provisioning, private readonly TenantLifecycleService $lifecycle) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize($request);
        $query = Tenant::query()->when($request->string('search')->toString(), fn ($query, $search) => $query->where(fn ($q) => $q->whereLike('name', "%{$search}%")->orWhereLike('tenant_code', "%{$search}%")))->when($request->string('status')->toString(), fn ($query, $status) => $query->where('status', $status))->withCount('members')->orderBy($request->input('sort', 'created_at'), $request->input('direction', 'desc'));

        return TenantResource::collection($query->paginate(min(max($request->integer('per_page', 25), 1), 100)))->response();
    }

    public function store(StoreTenantRequest $request): JsonResponse
    {
        return TenantResource::make($this->provisioning->provision($request->validated(), $request->user()))->response()->setStatusCode(201);
    }

    public function show(Request $request, Tenant $tenant): TenantResource
    {
        $this->authorize($request);
        return TenantResource::make($tenant);
    }

    public function update(StoreTenantRequest $request, Tenant $tenant): TenantResource
    {
        $tenant->fill($request->validated() + ['updated_by' => $request->user()->id])->save();
        return TenantResource::make($tenant->refresh());
    }

    public function suspend(Request $request, Tenant $tenant): TenantResource
    {
        $this->authorize($request);
        return TenantResource::make($this->lifecycle->transition($tenant, TenantStatus::Suspended, $request->user(), $request->string('reason')->toString() ?: null));
    }

    public function activate(Request $request, Tenant $tenant): TenantResource
    {
        $this->authorize($request);
        return TenantResource::make($this->lifecycle->transition($tenant, TenantStatus::Active, $request->user()));
    }

    public function usage(Request $request, Tenant $tenant): JsonResponse
    {
        $this->authorize($request);
        return response()->json(['data' => ['users' => $tenant->members()->count(), 'storage_bytes' => 0, 'api_requests' => 0, 'documents' => 0, 'ai_usage' => 0]]);
    }

    private function authorize(Request $request): void
    {
        abort_unless($request->user()?->is_platform_admin || $request->user()?->hasPermission('platform.tenants.manage'), 403);
    }
}
