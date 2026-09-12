<?php

namespace App\Domains\Platform\Http\Controllers;

use App\Domains\Platform\Contracts\TenantContext;
use App\Domains\Platform\Models\NavigationFavorite;
use App\Domains\Platform\Models\PlatformNotification;
use App\Domains\Platform\Models\RecentPage;
use App\Domains\Platform\Models\UserPreference;
use App\Domains\Platform\Models\UserProfile;
use App\Domains\Platform\Resources\NotificationResource;
use App\Domains\Platform\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExperienceController
{
    public function __construct(private readonly TenantContext $tenant) {}

    public function updateProfile(Request $request): UserResource
    {
        $data = $request->validate(['name' => ['sometimes', 'string', 'max:255'], 'locale' => ['sometimes', 'string', 'max:10'], 'timezone' => ['sometimes', 'nullable', 'timezone'], 'first_name' => ['sometimes', 'nullable', 'string', 'max:100'], 'last_name' => ['sometimes', 'nullable', 'string', 'max:100'], 'phone' => ['sometimes', 'nullable', 'string', 'max:40'], 'job_title' => ['sometimes', 'nullable', 'string', 'max:150'], 'theme' => ['sometimes', 'in:light,dark,system']]);
        $user = $request->user();
        $user->fill(array_intersect_key($data, array_flip(['name', 'locale', 'timezone'])))->save();
        UserProfile::query()->updateOrCreate(['user_id' => $user->id], ['tenant_id' => $this->tenant->id(), ...array_intersect_key($data, array_flip(['first_name', 'last_name', 'phone', 'job_title']))]);
        UserPreference::query()->updateOrCreate(['user_id' => $user->id], ['tenant_id' => $this->tenant->id(), ...array_intersect_key($data, array_flip(['theme', 'locale', 'timezone']))]);

        return UserResource::make($user->fresh()->load(['profile', 'preference', 'roles.permissions']));
    }

    public function notifications(Request $request): JsonResponse
    {
        return NotificationResource::collection(PlatformNotification::query()->where('tenant_id', $this->tenant->id())->where('user_id', $request->user()->id)->whereNull('archived_at')->latest()->paginate(min((int) $request->integer('per_page', 25), 100)))->response();
    }

    public function markRead(Request $request, string $notification): JsonResponse
    {
        $item = $this->notification($request, $notification);
        $item->update(['read_at' => now()]);

        return NotificationResource::make($item)->response();
    }

    public function favorites(Request $request): JsonResponse
    {
        return response()->json(['data' => NavigationFavorite::query()->where('tenant_id', $this->tenant->id())->where('user_id', $request->user()->id)->orderBy('position')->get()]);
    }

    public function storeFavorite(Request $request): JsonResponse
    {
        $data = $request->validate(['route_name' => ['required', 'string', 'max:160'], 'label' => ['required', 'string', 'max:160'], 'url' => ['required', 'string', 'max:500'], 'position' => ['sometimes', 'integer', 'min:0']]);
        $favorite = NavigationFavorite::query()->updateOrCreate(['user_id' => $request->user()->id, 'route_name' => $data['route_name']], ['tenant_id' => $this->tenant->id(), ...$data]);

        return response()->json(['data' => $favorite], 201);
    }

    public function recordRecent(Request $request): JsonResponse
    {
        $data = $request->validate(['route_name' => ['required', 'string', 'max:160'], 'label' => ['required', 'string', 'max:160'], 'url' => ['required', 'string', 'max:500']]);
        $recent = RecentPage::query()->updateOrCreate(['user_id' => $request->user()->id, 'route_name' => $data['route_name']], ['tenant_id' => $this->tenant->id(), ...$data, 'visited_at' => now()]);

        return response()->json(['data' => $recent]);
    }

    private function notification(Request $request, string $id): PlatformNotification
    {
        return PlatformNotification::query()->where('tenant_id', $this->tenant->id())->where('user_id', $request->user()->id)->findOrFail($id);
    }
}
