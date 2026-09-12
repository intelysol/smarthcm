<?php

namespace App\Domains\Career\Http\Controllers;

use App\Domains\Career\Models\CareerJobSkillRequirement;
use App\Domains\Career\Models\CareerSkill;
use App\Domains\Career\Models\CareerSkillCategory;
use App\Domains\Career\Models\CareerSkillCompetencyMapping;
use App\Domains\Career\Models\CareerSkillLevel;
use App\Domains\Career\Requests\SkillCreateRequest;
use App\Domains\Career\Resources\CareerSkillResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminSkillMasterController extends Controller
{
    public function skills(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = CareerSkill::query()->where('tenant_id', $user->tenant_id)->with('category');

        if ($request->filled('type')) {
            $query->where('skill_type', $request->query('type'));
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->query('category_id'));
        }

        return response()->json(CareerSkillResource::collection($query->paginate(20)));
    }

    public function storeSkill(SkillCreateRequest $request): JsonResponse
    {
        $user = $request->user();

        $skill = CareerSkill::query()->create(array_merge(
            $request->validated(),
            ['tenant_id' => $user->tenant_id, 'status' => 'active']
        ));

        return response()->json(['data' => new CareerSkillResource($skill->load('category'))], 201);
    }

    public function categories(Request $request): JsonResponse
    {
        $categories = CareerSkillCategory::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->withCount('skills')
            ->get();

        return response()->json(['data' => $categories]);
    }

    public function storeCategory(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
        ]);

        $category = CareerSkillCategory::query()->create(array_merge(
            $validated,
            ['tenant_id' => $request->user()->tenant_id, 'status' => 'active']
        ));

        return response()->json(['data' => $category], 201);
    }

    public function levels(Request $request): JsonResponse
    {
        $levels = CareerSkillLevel::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->orderBy('level_number')
            ->get();

        return response()->json(['data' => $levels]);
    }

    public function storeJobRequirement(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'job_id' => ['required', 'string', 'exists:organization_job_definitions,id'],
            'skill_id' => ['required', 'string', 'exists:career_skills,id'],
            'required_level' => ['required', 'integer', 'min:1', 'max:10'],
            'importance' => ['required', 'string', 'in:critical,high,medium,low'],
            'is_mandatory' => ['nullable', 'boolean'],
        ]);

        $req = CareerJobSkillRequirement::query()->updateOrCreate(
            [
                'tenant_id' => $request->user()->tenant_id,
                'job_id' => $validated['job_id'],
                'skill_id' => $validated['skill_id'],
            ],
            [
                'required_level' => $validated['required_level'],
                'importance' => $validated['importance'],
                'is_mandatory' => $validated['is_mandatory'] ?? true,
            ]
        );

        return response()->json(['data' => $req->load(['job', 'skill'])], 201);
    }
}
