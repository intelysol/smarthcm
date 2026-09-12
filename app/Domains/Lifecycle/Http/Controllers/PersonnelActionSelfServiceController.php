<?php

namespace App\Domains\Lifecycle\Http\Controllers;

use App\Domains\Lifecycle\Enums\AcknowledgementStatus;
use App\Domains\Lifecycle\Models\PersonnelActionAcknowledgement;
use App\Domains\Lifecycle\Models\PersonnelActionRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PersonnelActionSelfServiceController extends Controller
{
    public function employeeView(Request $request): View
    {
        $employeeId = $request->user()->employee_id;
        $actions = PersonnelActionRequest::where('employee_id', $employeeId)
            ->with(['actionType', 'changes', 'acknowledgement', 'documents'])
            ->latest()
            ->get();

        return view('lifecycle.employee.actions', compact('actions'));
    }

    public function getMyActions(Request $request): JsonResponse
    {
        $employeeId = $request->user()->employee_id;
        if (!$employeeId) {
            throw ValidationException::withMessages(['employee' => 'User does not have an associated employee profile.']);
        }

        $actions = PersonnelActionRequest::where('employee_id', $employeeId)
            ->with(['actionType', 'changes', 'acknowledgement', 'documents'])
            ->latest()
            ->get();

        return response()->json($actions);
    }

    public function acknowledge(Request $request, string $id): JsonResponse
    {
        $employeeId = $request->user()->employee_id;
        $action = PersonnelActionRequest::where('id', $id)
            ->where('employee_id', $employeeId)
            ->firstOrFail();

        $ack = PersonnelActionAcknowledgement::updateOrCreate(
            [
                'personnel_action_request_id' => $action->id,
                'employee_id' => $employeeId,
            ],
            [
                'tenant_id' => $action->tenant_id,
                'status' => AcknowledgementStatus::ACKNOWLEDGED->value,
                'acknowledged_at' => now(),
                'comment' => $request->input('comment'),
                'ip_address' => $request->ip(),
            ]
        );

        return response()->json($ack);
    }
}
