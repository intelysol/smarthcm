<?php

namespace App\Domains\Platform\Http\Controllers;

use App\Domains\Platform\Contracts\TenantContext;
use App\Domains\Platform\Models\Role;
use App\Domains\Platform\Repositories\RoleRepository;
use App\Domains\Platform\Requests\StoreRoleRequest;
use App\Domains\Platform\Resources\RoleResource;
use App\Domains\Platform\Services\RoleService;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoleController
{
    public function __construct(private readonly RoleRepository $roles, private readonly RoleService $service, private readonly TenantContext $tenant) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Role::class);

        return RoleResource::collection($this->roles->paginate($this->tenant->id(), $request->query()))->response();
    }

    public function store(StoreRoleRequest $request): JsonResponse
    {
        $this->authorize('create', Role::class);
        $data = $request->validated();
        $role = $this->service->create($this->tenant->id(), $request->user(), $data, $data['permission_ids'] ?? []);

        return RoleResource::make($role)->response()->setStatusCode(201);
    }

    public function syncPermissions(StoreRoleRequest $request, string $role): JsonResponse
    {
        $model = $this->roles->find($this->tenant->id(), $role);
        $this->authorize('update', $model);

        return RoleResource::make($this->service->syncPermissions($model, $request->user(), $request->validated('permission_ids', [])))->response();
    }

    public function assign(Request $request, string $role): JsonResponse
    {
        $model = $this->roles->find($this->tenant->id(), $role);
        $this->authorize('update', $model);
        $data = $request->validate(['user_id' => ['required', 'integer']]);
        $user = User::query()->where('tenant_id', $this->tenant->id())->findOrFail($data['user_id']);
        $this->service->assign($model, $user, $request->user());

        return response()->json(['message' => 'Role assigned.']);
    }

    private function authorize(string $ability, mixed $arguments): void
    {
        request()->user()->can($ability, $arguments) || abort(403);
    }
}
