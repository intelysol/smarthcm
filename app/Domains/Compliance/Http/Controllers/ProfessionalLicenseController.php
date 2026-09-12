<?php

namespace App\Domains\Compliance\Http\Controllers;

use App\Domains\Compliance\Services\ProfessionalLicenseService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfessionalLicenseController extends Controller
{
    public function __construct(
        protected ProfessionalLicenseService $licenseService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = (string) $request->user()->tenant_id;
        $filters = $request->only(['employee_id', 'status', 'expiring_days', 'search']);
        $licenses = $this->licenseService->listLicenses($tenantId, $filters, (int) $request->get('per_page', 25));

        return response()->json([
            'success' => true,
            'data' => $licenses,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|uuid',
            'compliance_requirement_id' => 'nullable|uuid',
            'license_name' => 'required|string|max:255',
            'license_number' => 'required|string|max:100',
            'license_type' => 'nullable|string|max:100',
            'licensing_board' => 'nullable|string|max:255',
            'issuing_authority' => 'nullable|string|max:255',
            'issuing_jurisdiction' => 'nullable|string|max:100',
            'state_province' => 'nullable|string|max:100',
            'issuing_country' => 'nullable|string|max:50',
            'country' => 'nullable|string|max:50',
            'issue_date' => 'nullable|date',
            'effective_from' => 'nullable|date',
            'expiration_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
            'document_id' => 'nullable|uuid',
            'notes' => 'nullable|string',
        ]);

        $data = [
            'compliance_requirement_id' => $validated['compliance_requirement_id'] ?? null,
            'license_name' => $validated['license_name'],
            'license_number' => $validated['license_number'],
            'license_type' => $validated['license_type'] ?? 'Professional License',
            'issuing_authority' => $validated['issuing_authority'] ?? ($validated['licensing_board'] ?? 'Licensing Board'),
            'state_province' => $validated['state_province'] ?? ($validated['issuing_jurisdiction'] ?? null),
            'country' => $validated['country'] ?? ($validated['issuing_country'] ?? 'USA'),
            'issue_date' => $validated['issue_date'] ?? null,
            'effective_from' => $validated['effective_from'] ?? ($validated['issue_date'] ?? null),
            'expiry_date' => $validated['expiry_date'] ?? ($validated['expiration_date'] ?? null),
            'document_id' => $validated['document_id'] ?? null,
        ];

        $license = $this->licenseService->addLicense(
            $validated['employee_id'],
            $data,
            $request->user()
        );

        return response()->json([
            'success' => true,
            'message' => 'Professional license recorded successfully.',
            'data' => $license,
        ], 201);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $license = $this->licenseService->getLicense((string) $request->user()->tenant_id, $id);

        if (!$license) {
            return response()->json([
                'success' => false,
                'message' => 'Professional license record not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $license,
        ]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'license_name' => 'sometimes|required|string|max:255',
            'license_number' => 'sometimes|required|string|max:100',
            'licensing_board' => 'sometimes|required|string|max:255',
            'issuing_jurisdiction' => 'sometimes|required|string|max:100',
            'issue_date' => 'sometimes|required|date',
            'expiration_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $license = $this->licenseService->updateLicense(
            (string) $request->user()->tenant_id,
            $id,
            $validated,
            (int) $request->user()->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Professional license updated successfully.',
            'data' => $license,
        ]);
    }

    public function registrations(Request $request): JsonResponse
    {
        $tenantId = (string) $request->user()->tenant_id;
        $filters = $request->only(['employee_id', 'status', 'search']);
        $registrations = $this->licenseService->listRegistrations($tenantId, $filters, (int) $request->get('per_page', 25));

        return response()->json([
            'success' => true,
            'data' => $registrations,
        ]);
    }

    public function storeRegistration(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|uuid',
            'compliance_requirement_id' => 'nullable|uuid',
            'body_name' => 'required|string|max:255',
            'registration_number' => 'required|string|max:100',
            'registration_type' => 'nullable|string|max:100',
            'jurisdiction' => 'required|string|max:100',
            'registration_date' => 'required|date',
            'expiration_date' => 'nullable|date|after_or_equal:registration_date',
            'document_id' => 'nullable|uuid',
            'notes' => 'nullable|string',
        ]);

        $reg = $this->licenseService->recordRegistration(
            (string) $request->user()->tenant_id,
            $validated,
            (int) $request->user()->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Regulatory registration recorded successfully.',
            'data' => $reg,
        ], 201);
    }
}
