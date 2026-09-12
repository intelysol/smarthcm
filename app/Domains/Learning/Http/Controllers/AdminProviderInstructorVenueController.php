<?php

namespace App\Domains\Learning\Http\Controllers;

use App\Domains\Learning\Models\LearningInstructor;
use App\Domains\Learning\Models\LearningProvider;
use App\Domains\Learning\Models\LearningVenue;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminProviderInstructorVenueController extends Controller
{
    public function providers(Request $request): JsonResponse
    {
        abort_unless($request->user()?->hasPermission('hcm.learning.provider.manage') || $request->user()?->hasPermission('hcm.learning.view'), 403);

        $providers = LearningProvider::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->paginate((int) $request->integer('per_page', 25));

        return response()->json($providers);
    }

    public function storeProvider(Request $request): JsonResponse
    {
        abort_unless($request->user()?->hasPermission('hcm.learning.provider.manage'), 403);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'in:internal,external,vendor,university,certification_body'],
            'contact' => ['nullable', 'string'],
            'email' => ['nullable', 'email'],
            'website' => ['nullable', 'url'],
            'description' => ['nullable', 'string'],
        ]);

        $provider = LearningProvider::query()->create([
            ...$request->all(),
            'tenant_id' => $request->user()->tenant_id,
            'status' => 'active',
        ]);

        return response()->json(['data' => $provider], 201);
    }

    public function instructors(Request $request): JsonResponse
    {
        abort_unless($request->user()?->hasPermission('hcm.learning.instructor.manage') || $request->user()?->hasPermission('hcm.learning.view'), 403);

        $instructors = LearningInstructor::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->with(['employee', 'provider'])
            ->paginate((int) $request->integer('per_page', 25));

        return response()->json($instructors);
    }

    public function storeInstructor(Request $request): JsonResponse
    {
        abort_unless($request->user()?->hasPermission('hcm.learning.instructor.manage'), 403);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'instructor_type' => ['nullable', 'string', 'in:employee,external,provider'],
            'employee_id' => ['nullable', 'uuid', 'exists:employees,id'],
            'provider_id' => ['nullable', 'uuid', 'exists:learning_providers,id'],
            'email' => ['nullable', 'email'],
            'bio' => ['nullable', 'string'],
            'expertise' => ['nullable', 'array'],
        ]);

        $instructor = LearningInstructor::query()->create([
            ...$request->all(),
            'tenant_id' => $request->user()->tenant_id,
            'status' => 'active',
        ]);

        return response()->json(['data' => $instructor], 201);
    }

    public function venues(Request $request): JsonResponse
    {
        abort_unless($request->user()?->hasPermission('hcm.learning.venue.manage') || $request->user()?->hasPermission('hcm.learning.view'), 403);

        $venues = LearningVenue::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->paginate((int) $request->integer('per_page', 25));

        return response()->json($venues);
    }

    public function storeVenue(Request $request): JsonResponse
    {
        abort_unless($request->user()?->hasPermission('hcm.learning.venue.manage'), 403);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'venue_type' => ['nullable', 'string', 'in:physical,virtual,external'],
            'location' => ['nullable', 'string'],
            'capacity' => ['nullable', 'integer', 'min:0'],
            'meeting_url_reference' => ['nullable', 'string'],
            'meeting_provider' => ['nullable', 'string'],
        ]);

        $venue = LearningVenue::query()->create([
            ...$request->all(),
            'tenant_id' => $request->user()->tenant_id,
            'status' => 'active',
        ]);

        return response()->json(['data' => $venue], 201);
    }
}
