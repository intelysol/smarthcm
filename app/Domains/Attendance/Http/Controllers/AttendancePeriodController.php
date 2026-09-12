<?php

namespace App\Domains\Attendance\Http\Controllers;

use App\Domains\Attendance\Models\AttendancePeriod;
use App\Domains\Attendance\Requests\AttendancePeriodRequest;
use App\Domains\Attendance\Services\AttendancePeriodService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendancePeriodController extends Controller
{
    public function __construct(
        protected AttendancePeriodService $periodService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $periods = AttendancePeriod::query()
            ->where('tenant_id', $user->tenant_id)
            ->withCount('timesheets')
            ->latest('start_date')
            ->get();

        return response()->json(['data' => $periods]);
    }

    public function store(AttendancePeriodRequest $request): JsonResponse
    {
        $user = $request->user();
        $period = $this->periodService->createPeriod($user->tenant_id, $request->validated(), $user);

        return response()->json([
            'message' => 'Attendance period created.',
            'data' => $period,
        ], 201);
    }

    public function lock(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $period = AttendancePeriod::query()->where('tenant_id', $user->tenant_id)->findOrFail($id);

        $locked = $this->periodService->lockPeriod($period, $user);

        return response()->json([
            'message' => 'Attendance period locked successfully.',
            'data' => $locked,
        ]);
    }

    public function reopen(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $request->validate(['reason' => ['required', 'string', 'max:500']]);

        $period = AttendancePeriod::query()->where('tenant_id', $user->tenant_id)->findOrFail($id);

        $reopened = $this->periodService->reopenPeriod($period, $user, $request->input('reason'));

        return response()->json([
            'message' => 'Attendance period reopened.',
            'data' => $reopened,
        ]);
    }
}
