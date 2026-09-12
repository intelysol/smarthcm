<?php

namespace App\Domains\PersonalData\Http\Controllers;

use App\Domains\Employee\Models\Employee;
use App\Domains\PersonalData\Services\AddressService;
use App\Domains\PersonalData\Services\DependentService;
use App\Domains\PersonalData\Services\EmergencyContactService;
use App\Domains\PersonalData\Services\EmployeeDataQualityService;
use App\Domains\PersonalData\Services\EmployeeIdentifierService;
use App\Domains\PersonalData\Services\PersonalDataSecurityService;
use App\Domains\PersonalData\Services\PersonalDataService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PersonalDataController extends Controller
{
    public function __construct(
        protected PersonalDataService $personalDataService,
        protected AddressService $addressService,
        protected EmergencyContactService $emergencyContactService,
        protected DependentService $dependentService,
        protected EmployeeIdentifierService $identifierService,
        protected EmployeeDataQualityService $qualityService,
        protected PersonalDataSecurityService $securityService
    ) {}

    /**
     * Get aggregate personal data bundle for an employee.
     */
    public function show(Request $request, string $employeeId): JsonResponse
    {
        $employee = Employee::findOrFail($employeeId);
        $user = $request->user();

        $personalData = $this->personalDataService->getOrCreate($employeeId, $employee->tenant_id);
        $addresses = $this->addressService->getAddresses($employeeId);
        $emergencyContacts = $this->emergencyContactService->getContacts($employeeId);
        $dependents = $this->dependentService->getDependents($employeeId);
        $identifiers = $this->identifierService->getIdentifiers($employeeId, $user);
        $quality = $this->qualityService->calculateQuality($employeeId);

        return response()->json([
            'status' => 'success',
            'data' => [
                'personal_data' => $personalData,
                'addresses' => $addresses,
                'emergency_contacts' => $emergencyContacts,
                'dependents' => $dependents,
                'identifiers' => $identifiers,
                'quality' => $quality,
            ],
        ]);
    }

    /**
     * Update employee personal data.
     */
    public function update(Request $request, string $employeeId): JsonResponse
    {
        $validated = $request->validate([
            'salutation' => 'nullable|string|max:20',
            'first_name' => 'nullable|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'preferred_name' => 'nullable|string|max:100',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|string|max:30',
            'marital_status' => 'nullable|string|max:30',
            'nationality' => 'nullable|string|max:100',
            'blood_group' => 'nullable|string|max:10',
            'religion' => 'nullable|string|max:50',
            'personal_email' => 'nullable|email|max:150',
            'personal_phone' => 'nullable|string|max:50',
        ]);

        $updated = $this->personalDataService->update($employeeId, $validated, $request->user());

        return response()->json([
            'status' => 'success',
            'message' => 'Personal data updated successfully.',
            'data' => $updated,
        ]);
    }

    // ADDRESSES

    public function listAddresses(string $employeeId): JsonResponse
    {
        $addresses = $this->addressService->getAddresses($employeeId);
        return response()->json(['status' => 'success', 'data' => $addresses]);
    }

    public function storeAddress(Request $request, string $employeeId): JsonResponse
    {
        $validated = $request->validate([
            'address_type' => 'required|string|in:home,mailing,permanent,temporary,work',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'state_province' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:30',
            'country' => 'required|string|max:100',
            'is_current' => 'boolean',
            'effective_from' => 'nullable|date',
            'effective_to' => 'nullable|date',
        ]);

        $address = $this->addressService->addAddress($employeeId, $validated, $request->user());

        return response()->json([
            'status' => 'success',
            'message' => 'Address added successfully.',
            'data' => $address,
        ], 201);
    }

    public function updateAddress(Request $request, string $addressId): JsonResponse
    {
        $validated = $request->validate([
            'address_type' => 'sometimes|string|in:home,mailing,permanent,temporary,work',
            'address_line_1' => 'sometimes|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'sometimes|string|max:100',
            'state_province' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:30',
            'country' => 'sometimes|string|max:100',
            'is_current' => 'boolean',
            'effective_from' => 'nullable|date',
            'effective_to' => 'nullable|date',
        ]);

        $address = $this->addressService->updateAddress($addressId, $validated, $request->user());

        return response()->json([
            'status' => 'success',
            'message' => 'Address updated successfully.',
            'data' => $address,
        ]);
    }

    public function destroyAddress(string $addressId): JsonResponse
    {
        $this->addressService->deleteAddress($addressId);
        return response()->json(['status' => 'success', 'message' => 'Address deleted successfully.']);
    }

    // EMERGENCY CONTACTS

    public function listEmergencyContacts(string $employeeId): JsonResponse
    {
        $contacts = $this->emergencyContactService->getContacts($employeeId);
        return response()->json(['status' => 'success', 'data' => $contacts]);
    }

    public function storeEmergencyContact(Request $request, string $employeeId): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'relationship' => 'required|string|max:50',
            'primary_phone' => 'required|string|max:50',
            'secondary_phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:150',
            'address' => 'nullable|string|max:255',
            'priority_order' => 'nullable|integer',
            'is_primary' => 'boolean',
        ]);

        $contact = $this->emergencyContactService->addContact($employeeId, $validated, $request->user());

        return response()->json([
            'status' => 'success',
            'message' => 'Emergency contact added successfully.',
            'data' => $contact,
        ], 201);
    }

    public function updateEmergencyContact(Request $request, string $contactId): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:150',
            'relationship' => 'sometimes|string|max:50',
            'primary_phone' => 'sometimes|string|max:50',
            'secondary_phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:150',
            'address' => 'nullable|string|max:255',
            'priority_order' => 'nullable|integer',
            'is_primary' => 'boolean',
        ]);

        $contact = $this->emergencyContactService->updateContact($contactId, $validated, $request->user());

        return response()->json([
            'status' => 'success',
            'message' => 'Emergency contact updated successfully.',
            'data' => $contact,
        ]);
    }

    public function destroyEmergencyContact(string $contactId): JsonResponse
    {
        $this->emergencyContactService->deleteContact($contactId);
        return response()->json(['status' => 'success', 'message' => 'Emergency contact deleted successfully.']);
    }

    // DEPENDENTS

    public function listDependents(string $employeeId): JsonResponse
    {
        $dependents = $this->dependentService->getDependents($employeeId);
        return response()->json(['status' => 'success', 'data' => $dependents]);
    }

    public function storeDependent(Request $request, string $employeeId): JsonResponse
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'relationship' => 'required|string|in:spouse,child,parent,other',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|string|max:30',
            'is_disabled' => 'boolean',
            'is_student' => 'boolean',
            'national_id' => 'nullable|string|max:100',
            'coverage_eligible' => 'boolean',
        ]);

        $dependent = $this->dependentService->addDependent($employeeId, $validated, $request->user());

        return response()->json([
            'status' => 'success',
            'message' => 'Dependent added successfully.',
            'data' => $dependent,
        ], 201);
    }

    public function updateDependent(Request $request, string $dependentId): JsonResponse
    {
        $validated = $request->validate([
            'first_name' => 'sometimes|string|max:100',
            'last_name' => 'sometimes|string|max:100',
            'relationship' => 'sometimes|string|in:spouse,child,parent,other',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|string|max:30',
            'is_disabled' => 'boolean',
            'is_student' => 'boolean',
            'national_id' => 'nullable|string|max:100',
            'coverage_eligible' => 'boolean',
        ]);

        $dependent = $this->dependentService->updateDependent($dependentId, $validated, $request->user());

        return response()->json([
            'status' => 'success',
            'message' => 'Dependent updated successfully.',
            'data' => $dependent,
        ]);
    }

    public function destroyDependent(string $dependentId): JsonResponse
    {
        $this->dependentService->deleteDependent($dependentId);
        return response()->json(['status' => 'success', 'message' => 'Dependent deleted successfully.']);
    }

    // IDENTIFIERS

    public function listIdentifiers(Request $request, string $employeeId): JsonResponse
    {
        $identifiers = $this->identifierService->getIdentifiers($employeeId, $request->user());
        return response()->json(['status' => 'success', 'data' => $identifiers]);
    }

    public function storeIdentifier(Request $request, string $employeeId): JsonResponse
    {
        $validated = $request->validate([
            'identifier_type' => 'required|string|in:national_id,passport,tax_id,driving_license,social_security,other',
            'identifier_value' => 'required|string|max:150',
            'issuing_country' => 'nullable|string|max:100',
            'issue_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
            'is_primary' => 'boolean',
            'verification_status' => 'nullable|string|in:unverified,pending,verified,rejected',
        ]);

        $identifier = $this->identifierService->addIdentifier($employeeId, $validated, $request->user());

        return response()->json([
            'status' => 'success',
            'message' => 'Identifier added successfully.',
            'data' => $identifier,
        ], 201);
    }

    public function updateIdentifier(Request $request, string $identifierId): JsonResponse
    {
        $validated = $request->validate([
            'identifier_type' => 'sometimes|string|in:national_id,passport,tax_id,driving_license,social_security,other',
            'identifier_value' => 'sometimes|string|max:150',
            'issuing_country' => 'nullable|string|max:100',
            'issue_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
            'is_primary' => 'boolean',
            'verification_status' => 'nullable|string|in:unverified,pending,verified,rejected',
        ]);

        $identifier = $this->identifierService->updateIdentifier($identifierId, $validated, $request->user());

        return response()->json([
            'status' => 'success',
            'message' => 'Identifier updated successfully.',
            'data' => $identifier,
        ]);
    }

    public function destroyIdentifier(string $identifierId): JsonResponse
    {
        $this->identifierService->deleteIdentifier($identifierId);
        return response()->json(['status' => 'success', 'message' => 'Identifier deleted successfully.']);
    }
}
