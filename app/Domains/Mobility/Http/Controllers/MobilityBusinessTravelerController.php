<?php

namespace App\Domains\Mobility\Http\Controllers;

use App\Domains\Mobility\Models\MobilityBusinessTraveler;
use App\Domains\Mobility\Services\BusinessTravelerService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobilityBusinessTravelerController extends Controller
{
    public function __construct(
        protected BusinessTravelerService $travelerService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? $request->header('X-Tenant-ID');
        $travelers = MobilityBusinessTraveler::when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->with(['employee'])
            ->latest()
            ->paginate(25);

        return response()->json($travelers);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tenant_id' => 'sometimes|uuid',
            'employee_id' => 'required|uuid',
            'destination_country' => 'required|string|max:80',
            'destination_city' => 'nullable|string|max:100',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'business_purpose' => 'required|string|max:200',
            'visa_required' => 'sometimes|boolean',
            'visa_cleared' => 'sometimes|boolean',
            'expense_travel_request_id' => 'nullable|uuid',
        ]);

        $tenantId = $validated['tenant_id'] ?? $request->user()?->tenant_id ?? $request->header('X-Tenant-ID');

        $trip = $this->travelerService->registerTrip(array_merge($validated, ['tenant_id' => $tenantId]));

        return response()->json($trip, 201);
    }

    public function show(MobilityBusinessTraveler $businessTraveler): JsonResponse
    {
        return response()->json($businessTraveler->load('employee'));
    }
}
