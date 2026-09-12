<?php

declare(strict_types=1);

namespace App\Domains\HealthSafety\Http\Controllers;

use App\Domains\HealthSafety\Models\HcmWorkplaceAccommodation;
use App\Domains\HealthSafety\Services\WorkplaceAccommodationService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkplaceAccommodationController extends Controller
{
    public function __construct(
        protected WorkplaceAccommodationService $service
    ) {}

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company_id' => 'required|uuid',
            'employee_id' => 'required|uuid',
            'restriction_id' => 'nullable|uuid',
            'rtw_case_id' => 'nullable|uuid',
            'accommodation_type' => 'required|in:ergonomic_equipment,schedule_modification,job_restructuring,physical_facility,technology_aid,other',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'cost_estimate' => 'nullable|numeric',
            'equipment_needed' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'review_date' => 'nullable|date',
        ]);

        $validated['tenant_id'] = $request->user()?->tenant_id ?? 'default';

        $accommodation = $this->service->requestAccommodation($validated, $request->user()?->id);

        return response()->json([
            'status' => 'success',
            'message' => 'Workplace accommodation requested successfully.',
            'data' => $accommodation,
        ], 201);
    }

    public function assess(Request $request, string $id): JsonResponse
    {
        $accommodation = HcmWorkplaceAccommodation::findOrFail($id);

        $validated = $request->validate([
            'approved' => 'required|boolean',
            'cost_actual' => 'nullable|numeric',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'review_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $updated = $this->service->assessAccommodation(
            $accommodation,
            (bool) $validated['approved'],
            $validated,
            (int) $request->user()?->id
        );

        return response()->json([
            'status' => 'success',
            'message' => $validated['approved'] ? 'Accommodation approved.' : 'Accommodation rejected.',
            'data' => $updated,
        ]);
    }

    public function implement(Request $request, string $id): JsonResponse
    {
        $accommodation = HcmWorkplaceAccommodation::findOrFail($id);

        $validated = $request->validate([
            'start_date' => 'nullable|date',
            'cost_actual' => 'nullable|numeric',
        ]);

        $updated = $this->service->implementAccommodation($accommodation, $validated, (int) $request->user()?->id);

        return response()->json([
            'status' => 'success',
            'message' => 'Accommodation marked as implemented in workplace.',
            'data' => $updated,
        ]);
    }
}
