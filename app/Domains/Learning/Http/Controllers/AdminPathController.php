<?php

namespace App\Domains\Learning\Http\Controllers;

use App\Domains\Learning\Models\LearningPath;
use App\Domains\Learning\Services\LearningProgramPathService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminPathController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()?->hasPermission('hcm.learning.path.view'), 403);

        $paths = LearningPath::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->with('items')
            ->paginate((int) $request->integer('per_page', 25));

        return response()->json($paths);
    }

    public function store(Request $request, LearningProgramPathService $service): JsonResponse
    {
        abort_unless($request->user()?->hasPermission('hcm.learning.path.manage'), 403);

        $request->validate([
            'code' => ['required', 'string', 'max:50'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'target_role' => ['nullable', 'string', 'max:100'],
            'rule_type' => ['nullable', 'string', 'in:all_required,minimum_credits,minimum_courses'],
        ]);

        $path = $service->createPath($request->user(), $request->all());

        return response()->json(['data' => $path], 201);
    }

    public function addItem(Request $request, LearningPath $path, LearningProgramPathService $service): JsonResponse
    {
        abort_unless($request->user()?->hasPermission('hcm.learning.path.manage'), 403);
        abort_unless($path->tenant_id === $request->user()->tenant_id, 404);

        $request->validate([
            'item_type' => ['required', 'string', 'in:course,program,assessment'],
            'item_id' => ['required', 'uuid'],
            'requirement_level' => ['nullable', 'string', 'in:required,optional,recommended,elective'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $item = $service->addItemToPath(
            $path,
            $request->string('item_type')->toString(),
            $request->string('item_id')->toString(),
            $request->string('requirement_level', 'required')->toString(),
            $request->integer('sort_order', 0)
        );

        return response()->json(['data' => $item], 201);
    }
}
