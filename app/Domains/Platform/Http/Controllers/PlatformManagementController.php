<?php

namespace App\Domains\Platform\Http\Controllers;

use App\Domains\Platform\Contracts\TenantContext;
use App\Domains\Platform\Models\FeatureFlag;
use App\Domains\Platform\Models\LoginHistory;
use App\Domains\Platform\Models\Setting;
use App\Domains\Shared\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlatformManagementController
{
    public function __construct(private readonly TenantContext $tenant) {}

    public function activities(Request $request): JsonResponse
    {
        $this->allow($request, 'platform.audit.view');

        return response()->json(ActivityLog::query()->where('tenant_id', $this->tenant->id())->latest('occurred_at')->paginate($this->perPage($request)));
    }

    public function loginHistory(Request $request): JsonResponse
    {
        $this->allow($request, 'platform.audit.view');

        return response()->json(LoginHistory::query()->where('tenant_id', $this->tenant->id())->latest('occurred_at')->paginate($this->perPage($request)));
    }

    public function flags(Request $request): JsonResponse
    {
        $this->allow($request, 'platform.settings.manage');

        return response()->json(['data' => FeatureFlag::query()->where(fn ($query) => $query->where('tenant_id', $this->tenant->id())->orWhereNull('tenant_id'))->orderBy('key')->get()]);
    }

    public function settings(Request $request): JsonResponse
    {
        $this->allow($request, 'platform.settings.manage');

        return response()->json(['data' => Setting::query()->where('tenant_id', $this->tenant->id())->orderBy('group')->orderBy('key')->get()]);
    }

    public function updateSetting(Request $request, string $group, string $key): JsonResponse
    {
        $this->allow($request, 'platform.settings.manage');
        $data = $request->validate(['value' => ['nullable'], 'type' => ['sometimes', 'in:string,boolean,integer,number,array,json'], 'is_encrypted' => ['sometimes', 'boolean']]);
        $setting = Setting::query()->updateOrCreate(['tenant_id' => $this->tenant->id(), 'group' => $group, 'key' => $key], [...$data, 'updated_by' => $request->user()->id, 'created_by' => $request->user()->id]);

        return response()->json(['data' => $setting]);
    }

    private function perPage(Request $request): int
    {
        return min(max($request->integer('per_page', 25), 1), 100);
    }

    private function allow(Request $request, string $permission): void
    {
        $request->user()->hasPermission($permission) || abort(403);
    }
}
