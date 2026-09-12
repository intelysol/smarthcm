<?php

namespace App\Domains\SelfService\Http\Controllers;

use App\Domains\SelfService\Models\EmployeePreference;
use App\Domains\SelfService\Models\ProfileChangeRequest;
use App\Domains\SelfService\Services\PortalService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function show(Request $request, PortalService $portal): JsonResponse
    {
        $user = $request->user();
        abort_if($user === null || ! $user->hasPermission('ess.profile'), 403);

        return response()->json(['data' => $portal->employeeFor($user)]);
    }

    public function update(Request $request, PortalService $portal): JsonResponse
    {
        $user = $request->user();
        abort_if($user === null || ! $user->hasPermission('ess.profile'), 403);
        $validated = $request->validate([
            'preferred_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'personal_email' => ['sometimes', 'nullable', 'email:rfc', 'max:255'],
            'mobile' => ['sometimes', 'nullable', 'string', 'max:40'],
            'alternate_mobile' => ['sometimes', 'nullable', 'string', 'max:40'],
            'office_phone' => ['sometimes', 'nullable', 'string', 'max:40'],
            'present_address' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'permanent_address' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'country' => ['sometimes', 'nullable', 'string', 'max:120'],
            'state' => ['sometimes', 'nullable', 'string', 'max:120'],
            'city' => ['sometimes', 'nullable', 'string', 'max:120'],
            'postal_code' => ['sometimes', 'nullable', 'string', 'max:40'],
            'photo_path' => ['sometimes', 'nullable', 'string', 'max:255'],
        ]);
        $employee = $portal->employeeFor($user);
        $employee->update($validated);

        return response()->json(['data' => $employee->refresh()]);
    }

    public function changePassword(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_if($user === null || ! $user->hasPermission('ess.profile'), 403);
        $validated = $request->validate(['current_password' => ['required', 'current_password'], 'password' => ['required', 'confirmed', 'min:12']]);
        $user->forceFill(['password' => Hash::make($validated['password'])])->save();

        return response()->json(['message' => 'Password changed successfully.']);
    }

    public function updatePreferences(Request $request, PortalService $portal): JsonResponse
    {
        $user = $request->user();
        abort_if($user === null || ! $user->hasPermission('ess.profile'), 403);
        $validated = $request->validate(['language' => ['sometimes', 'string', 'max:10'], 'theme' => ['sometimes', 'in:light,dark,system'], 'timezone' => ['sometimes', 'nullable', 'timezone'], 'notification_preferences' => ['sometimes', 'array'], 'email_preferences' => ['sometimes', 'array'], 'mobile_preferences' => ['sometimes', 'array']]);
        $employee = $portal->employeeFor($user);
        $preferences = EmployeePreference::query()->updateOrCreate(['employee_id' => $employee->id], array_merge($validated, ['tenant_id' => $employee->tenant_id]));

        return response()->json(['data' => $preferences]);
    }

    public function requestChange(Request $request, PortalService $portal): JsonResponse
    {
        $user = $request->user(); abort_if($user === null || ! $user->hasPermission('ess.profile'), 403);
        $data = $request->validate(['request_type' => ['required','string','max:80'], 'requested_changes' => ['required','array','min:1'], 'reason' => ['nullable','string','max:2000']]);
        $employee = $portal->employeeFor($user);
        $change = ProfileChangeRequest::query()->create([...$data, 'tenant_id' => $employee->tenant_id, 'employee_id' => $employee->id, 'requested_by' => $user->id, 'status' => 'submitted']);
        return response()->json(['data' => $change], 201);
    }
}
