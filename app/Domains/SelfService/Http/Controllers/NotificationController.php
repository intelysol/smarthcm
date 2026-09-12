<?php

namespace App\Domains\SelfService\Http\Controllers;

use App\Domains\SelfService\Models\PortalNotification;
use App\Domains\SelfService\Services\PortalService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request, PortalService $portal): JsonResponse
    {
        $user = $request->user();
        abort_if($user === null || ! $user->hasPermission('notification.view'), 403);
        $employee = $portal->employeeFor($user);
        $query = PortalNotification::query()->where('employee_id', $employee->id);

        if ($request->boolean('archived')) {
            $query->whereNotNull('archived_at');
        } else {
            $query->whereNull('archived_at');
        }
        if ($request->filled('unread')) {
            $query->whereNull('read_at');
        }
        if ($request->filled('search')) {
            $query->where(fn ($builder) => $builder->where('title', 'like', '%'.$request->string('search').'%')->orWhere('body', 'like', '%'.$request->string('search').'%'));
        }

        return response()->json($query->latest()->paginate((int) $request->input('per_page', 15)));
    }

    public function markRead(Request $request, PortalService $portal, string $notification): JsonResponse
    {
        $user = $request->user();
        abort_if($user === null || ! $user->hasPermission('notification.view'), 403);
        $record = PortalNotification::query()->where('employee_id', $portal->employeeFor($user)->id)->findOrFail($notification);
        $record->update(['read_at' => now()]);

        return response()->json(['data' => $record->refresh()]);
    }

    public function archive(Request $request, PortalService $portal, string $notification): JsonResponse
    {
        $user = $request->user();
        abort_if($user === null || ! $user->hasPermission('notification.view'), 403);
        $record = PortalNotification::query()->where('employee_id', $portal->employeeFor($user)->id)->findOrFail($notification);
        $record->update(['archived_at' => now()]);

        return response()->json(['data' => $record->refresh()]);
    }
}
