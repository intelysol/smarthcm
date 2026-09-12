<?php

namespace App\Domains\Platform\Http\Controllers;

use App\Domains\Platform\Contracts\TenantContext;
use App\Domains\Platform\Models\TenantBranding;
use App\Domains\Platform\Models\TenantFeature;
use App\Domains\Platform\Models\TenantInvitation;
use App\Domains\Platform\Models\TenantSetting;
use App\Domains\Platform\Resources\TenantResource;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TenantExperienceController
{
    public function __construct(private readonly TenantContext $context) {}

    public function tenants(Request $request): JsonResponse
    {
        $tenants = $request->user()->is_platform_admin ? Tenant::query()->where('is_active', true)->get() : $request->user()->tenants()->wherePivot('status', 'active')->where('is_active', true)->get();
        return TenantResource::collection($tenants)->response();
    }

    public function switch(Request $request): JsonResponse
    {
        $data = $request->validate(['tenant' => ['required', 'string']]);
        $tenant = Tenant::query()->where(fn ($query) => $query->where('id', $data['tenant'])->orWhere('uuid', $data['tenant'])->orWhere('slug', $data['tenant']))->firstOrFail();
        abort_unless($tenant->isAccessible() && $request->user()->canAccessTenant($tenant), 403);
        $request->session()->regenerate();
        $request->session()->put('tenant_uuid', $tenant->uuid ?? $tenant->id);

        return response()->json(['data' => TenantResource::make($tenant), 'message' => 'Tenant context switched.']);
    }

    public function settings(Request $request): JsonResponse
    {
        return response()->json(['data' => TenantSetting::query()->firstOrCreate(['tenant_id' => $this->context->id()], ['configuration' => []])->configuration]);
    }

    public function updateSettings(Request $request): JsonResponse
    {
        $this->manage($request);
        $data = $request->validate(['configuration' => ['required', 'array']]);
        $record = TenantSetting::query()->updateOrCreate(['tenant_id' => $this->context->id()], $data);
        return response()->json(['data' => $record->configuration]);
    }

    public function branding(Request $request): JsonResponse
    {
        return response()->json(['data' => TenantBranding::query()->firstOrCreate(['tenant_id' => $this->context->id()], ['configuration' => []])->configuration]);
    }

    public function updateBranding(Request $request): JsonResponse
    {
        $this->manage($request);
        $data = $request->validate(['configuration' => ['required', 'array']]);
        $record = TenantBranding::query()->updateOrCreate(['tenant_id' => $this->context->id()], $data);
        return response()->json(['data' => $record->configuration]);
    }

    public function features(Request $request): JsonResponse
    {
        return response()->json(['data' => TenantFeature::query()->firstOrCreate(['tenant_id' => $this->context->id()], ['configuration' => []])->configuration]);
    }

    public function updateFeatures(Request $request): JsonResponse
    {
        $this->manage($request);
        $data = $request->validate(['configuration' => ['required', 'array']]);
        $record = TenantFeature::query()->updateOrCreate(['tenant_id' => $this->context->id()], $data);
        return response()->json(['data' => $record->configuration]);
    }

    public function invitations(Request $request): JsonResponse
    {
        $this->manage($request);
        return response()->json(['data' => TenantInvitation::query()->where('tenant_id', $this->context->id())->latest()->paginate(25)]);
    }

    public function invite(Request $request): JsonResponse
    {
        $this->manage($request);
        $data = $request->validate(['email' => ['required', 'email'], 'role_placeholder' => ['nullable', 'string', 'max:100']]);
        $invitation = TenantInvitation::query()->create([...$data, 'tenant_id' => $this->context->id(), 'invited_by' => $request->user()->id, 'token' => Str::random(64), 'expires_at' => now()->addDays(7)]);
        return response()->json(['data' => $invitation], 201);
    }

    public function resend(Request $request, TenantInvitation $invitation): JsonResponse
    {
        $this->manage($request); abort_unless($invitation->tenant_id === $this->context->id(), 404);
        $invitation->update(['token' => Str::random(64), 'status' => 'pending', 'expires_at' => now()->addDays(7)]);
        return response()->json(['data' => $invitation]);
    }

    public function destroy(Request $request, TenantInvitation $invitation): JsonResponse
    {
        $this->manage($request); abort_unless($invitation->tenant_id === $this->context->id(), 404);
        $invitation->update(['status' => 'revoked']);
        return response()->json([], 204);
    }

    private function manage(Request $request): void
    {
        abort_unless($request->user()?->hasPermission('platform.settings.manage') || $request->user()?->is_platform_admin, 403);
    }
}
