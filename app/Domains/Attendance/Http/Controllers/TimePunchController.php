<?php

namespace App\Domains\Attendance\Http\Controllers;

use App\Domains\Attendance\Models\AttendanceRawEvent;
use App\Domains\Attendance\Services\AttendanceNormalizer;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TimePunchController extends Controller
{
    public function punch(Request $request, AttendanceNormalizer $normalizer): JsonResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|uuid',
            'event_type' => 'required|string',
            'event_timestamp' => 'nullable|date',
            'source' => 'nullable|string',
            'idempotency_key' => 'nullable|string',
        ]);

        $tenantId = $request->user()?->tenant_id ?? $request->input('tenant_id');
        $idempotencyKey = $validated['idempotency_key'] ?? ('PUNCH-' . Str::uuid());

        // Idempotency check
        $existing = AttendanceRawEvent::where('tenant_id', $tenantId)
            ->where('idempotency_key', $idempotencyKey)
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Punch event already processed (idempotent duplicate)',
                'raw_event_id' => $existing->id,
                'is_duplicate' => true,
            ], 200);
        }

        $timestamp = isset($validated['event_timestamp']) ? Carbon::parse($validated['event_timestamp']) : now();

        $rawEvent = AttendanceRawEvent::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenantId,
            'employee_device_identifier' => $validated['employee_id'],
            'event_timestamp' => $timestamp,
            'event_type' => $validated['event_type'],
            'raw_payload' => $request->all(),
            'received_at' => now(),
            'source' => $validated['source'] ?? 'web',
            'idempotency_key' => $idempotencyKey,
            'is_processed' => false,
            'created_at' => now(),
        ]);

        $normalized = $normalizer->normalize($rawEvent);

        return response()->json([
            'message' => 'Punch event accepted and normalized',
            'raw_event_id' => $rawEvent->id,
            'normalized_event_id' => $normalized?->id,
            'is_duplicate' => false,
        ], 201);
    }
}