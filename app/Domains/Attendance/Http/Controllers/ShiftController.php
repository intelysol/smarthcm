<?php

namespace App\Domains\Attendance\Http\Controllers;

use App\Domains\Attendance\Models\ShiftDefinition;
use App\Domains\Attendance\Models\ShiftPattern;
use App\Domains\Attendance\Requests\ShiftRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $shifts = ShiftDefinition::query()
            ->where('tenant_id', $user->tenant_id)
            ->with('breaks')
            ->orderBy('name')
            ->get();

        return response()->json(['data' => $shifts]);
    }

    public function store(ShiftRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();
        $data['tenant_id'] = $user->tenant_id;
        $data['created_by'] = $user->id;

        $shift = ShiftDefinition::query()->create($data);

        return response()->json([
            'message' => 'Shift definition created successfully.',
            'data' => $shift,
        ], 201);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $shift = ShiftDefinition::query()
            ->where('tenant_id', $user->tenant_id)
            ->with('breaks')
            ->findOrFail($id);

        return response()->json(['data' => $shift]);
    }

    public function update(ShiftRequest $request, string $id): JsonResponse
    {
        $user = $request->user();
        $shift = ShiftDefinition::query()->where('tenant_id', $user->tenant_id)->findOrFail($id);
        $data = $request->validated();
        $data['updated_by'] = $user->id;

        $shift->update($data);

        return response()->json([
            'message' => 'Shift definition updated successfully.',
            'data' => $shift->fresh(['breaks']),
        ]);
    }

    public function patterns(Request $request): JsonResponse
    {
        $user = $request->user();
        $patterns = ShiftPattern::query()
            ->where('tenant_id', $user->tenant_id)
            ->with('patternDays.shift')
            ->get();

        return response()->json(['data' => $patterns]);
    }
}
