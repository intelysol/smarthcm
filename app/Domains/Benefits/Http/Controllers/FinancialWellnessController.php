<?php

namespace App\Domains\Benefits\Http\Controllers;

use App\Domains\Benefits\Models\FinancialWellnessProgram;
use App\Domains\Benefits\Services\FinancialWellnessService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FinancialWellnessController extends Controller
{
    public function __construct(
        protected FinancialWellnessService $wellnessService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;
        $programs = FinancialWellnessProgram::where('tenant_id', $tenantId)
            ->with('resources')
            ->latest()
            ->get();

        return response()->json($programs);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:60'],
            'name' => ['required', 'string', 'max:150'],
            'program_type' => ['required', 'string', 'max:40'],
            'description' => ['nullable', 'string'],
            'start_date' => ['required', 'date'],
        ]);

        $program = $this->wellnessService->createProgram(array_merge($validated, [
            'tenant_id' => $request->user()->tenant_id,
            'created_by' => $request->user()->id,
        ]));

        return response()->json($program, 201);
    }
}
