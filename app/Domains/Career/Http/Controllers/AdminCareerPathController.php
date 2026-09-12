<?php

namespace App\Domains\Career\Http\Controllers;

use App\Domains\Career\Models\CareerPath;
use App\Domains\Career\Models\CareerPathStep;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminCareerPathController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $paths = CareerPath::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->with(['department', 'steps.job'])
            ->get();

        return response()->json(['data' => $paths]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50'],
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'department_id' => ['nullable', 'string', 'exists:departments,id'],
            'job_family' => ['nullable', 'string'],
        ]);

        $path = CareerPath::query()->create(array_merge(
            $validated,
            ['tenant_id' => $request->user()->tenant_id, 'status' => 'active']
        ));

        return response()->json(['data' => $path], 201);
    }

    public function storeStep(Request $request, string $pathId): JsonResponse
    {
        $path = CareerPath::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->findOrFail($pathId);

        $validated = $request->validate([
            'job_id' => ['required', 'string', 'exists:organization_job_definitions,id'],
            'career_level' => ['nullable', 'string'],
            'sequence' => ['required', 'integer', 'min:1'],
            'minimum_experience_years' => ['nullable', 'numeric', 'min:0'],
            'performance_min_rating' => ['nullable', 'numeric', 'min:1', 'max:5'],
            'required_skills' => ['nullable', 'array'],
            'required_competencies' => ['nullable', 'array'],
            'required_certifications' => ['nullable', 'array'],
        ]);

        $step = CareerPathStep::query()->create(array_merge(
            $validated,
            ['tenant_id' => $request->user()->tenant_id, 'career_path_id' => $path->id]
        ));

        return response()->json(['data' => $step->load('job')], 201);
    }
}
