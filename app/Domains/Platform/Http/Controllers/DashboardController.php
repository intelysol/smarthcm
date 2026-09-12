<?php

namespace App\Domains\Platform\Http\Controllers;

use App\Domains\Organization\Models\Company;
use App\Domains\Platform\Contracts\TenantContext;
use App\Domains\Platform\Models\PlatformNotification;
use App\Domains\Shared\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController
{
    public function __construct(private readonly TenantContext $tenant) {}

    public function __invoke(Request $request): JsonResponse
    {
        $tenantId = $this->tenant->id();

        return response()->json(['data' => ['statistics' => ['users' => User::query()->where('tenant_id', $tenantId)->where('status', 'active')->count(), 'companies' => Company::query()->where('tenant_id', $tenantId)->count(), 'unread_notifications' => PlatformNotification::query()->where('tenant_id', $tenantId)->where('user_id', $request->user()->id)->whereNull('read_at')->count()], 'recent_activities' => ActivityLog::query()->where('tenant_id', $tenantId)->latest('occurred_at')->limit(10)->get(['id', 'action', 'table_name', 'record_id', 'occurred_at']), 'quick_actions' => ['platform.users.create', 'platform.roles.create', 'organization.companies.create']]]);
    }
}
