<?php

namespace App\Domains\SelfService\Http\Controllers;

use App\Domains\Employee\Models\Employee;
use App\Domains\SelfService\Services\ManagerPortalService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ManagerPortalController extends Controller
{
    public function __construct(
        protected ManagerPortalService $managerService
    ) {}

    public function dashboard(Request $request): View|JsonResponse
    {
        $user = $request->user();
        $manager = Employee::where('tenant_id', $user->tenant_id)->where(function ($q) use ($user) {
            $q->where('user_id', $user->id)->orWhere('id', $user->employee_id);
        })->first() ?? Employee::where('tenant_id', $user->tenant_id)->first();

        if (! $manager) {
            return response()->json(['message' => 'Manager profile not found'], 404);
        }

        $data = $this->managerService->getManagerDashboard($manager);

        if ($request->wantsJson()) {
            return response()->json($data);
        }

        return view('self-service.manager.dashboard', $data);
    }
}
