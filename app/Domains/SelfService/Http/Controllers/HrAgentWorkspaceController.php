<?php

namespace App\Domains\SelfService\Http\Controllers;

use App\Domains\SelfService\Enums\ServiceRequestStatus;
use App\Domains\SelfService\Models\HrServiceQueue;
use App\Domains\SelfService\Models\HrServiceRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HrAgentWorkspaceController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? 'default';

        $queues = HrServiceQueue::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->withCount(['requests' => fn ($q) => $q->whereNotIn('status', [ServiceRequestStatus::RESOLVED->value, ServiceRequestStatus::CLOSED->value])])
            ->get();

        $assignedToMe = HrServiceRequest::where('tenant_id', $tenantId)
            ->where('assigned_user_id', $request->user()?->id)
            ->whereNotIn('status', [ServiceRequestStatus::RESOLVED->value, ServiceRequestStatus::CLOSED->value])
            ->with(['service', 'employee', 'slaInstance'])
            ->orderBy('created_at', 'desc')
            ->get();

        $unassigned = HrServiceRequest::where('tenant_id', $tenantId)
            ->whereNull('assigned_user_id')
            ->whereNotIn('status', [ServiceRequestStatus::RESOLVED->value, ServiceRequestStatus::CLOSED->value, ServiceRequestStatus::DRAFT->value])
            ->with(['service', 'employee'])
            ->orderBy('created_at', 'desc')
            ->get();

        if ($request->wantsJson()) {
            return response()->json([
                'queues' => $queues,
                'assigned_to_me' => $assignedToMe,
                'unassigned' => $unassigned,
            ]);
        }

        return view('self-service.agent.workspace', compact('queues', 'assignedToMe', 'unassigned'));
    }
}
