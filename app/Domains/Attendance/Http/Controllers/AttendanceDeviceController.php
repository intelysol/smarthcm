<?php

namespace App\Domains\Attendance\Http\Controllers;

use App\Domains\Attendance\Models\AttendanceDevice;
use App\Domains\Attendance\Requests\AttendanceDeviceRequest;
use App\Domains\Attendance\Services\AttendanceDeviceService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceDeviceController extends Controller
{
    public function __construct(
        protected AttendanceDeviceService $deviceService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $devices = AttendanceDevice::query()
            ->where('tenant_id', $user->tenant_id)
            ->withCount('rawEvents')
            ->latest()
            ->get();

        return response()->json(['data' => $devices]);
    }

    public function store(AttendanceDeviceRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();
        $data['tenant_id'] = $user->tenant_id;
        $data['created_by'] = $user->id;

        $device = AttendanceDevice::query()->create($data);

        return response()->json([
            'message' => 'Attendance device registered successfully.',
            'data' => $device,
        ], 201);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $device = AttendanceDevice::query()
            ->where('tenant_id', $user->tenant_id)
            ->with('syncRuns')
            ->findOrFail($id);

        return response()->json(['data' => $device]);
    }

    public function sync(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $device = AttendanceDevice::query()->where('tenant_id', $user->tenant_id)->findOrFail($id);

        $syncRun = $this->deviceService->syncDevice($device, $user);

        return response()->json([
            'message' => 'Device synchronization executed.',
            'sync_run' => $syncRun,
        ]);
    }

    public function testConnection(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $device = AttendanceDevice::query()->where('tenant_id', $user->tenant_id)->findOrFail($id);

        $connected = $this->deviceService->testConnection($device);

        return response()->json([
            'connected' => $connected,
            'message' => $connected ? 'Device connection verified successfully.' : 'Could not connect to attendance terminal.',
        ]);
    }
}
