<?php

namespace App\Domains\Performance\Http\Controllers;

use App\Domains\Performance\Models\PerformanceCycle;
use App\Domains\Performance\Requests\PerformanceCycleConfigurationRequest;
use App\Domains\Performance\Requests\PerformanceCycleRequest;
use App\Domains\Performance\Resources\PerformanceCycleResource;
use App\Domains\Performance\Services\PerformanceCycleService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PerformanceCycleController extends Controller
{
    public function index(Request $request): JsonResponse { Gate::authorize('viewAny', PerformanceCycle::class); return PerformanceCycleResource::collection(PerformanceCycle::query()->with('configuration')->orderByDesc('start_date')->paginate((int) $request->integer('per_page', 25)))->response(); }
    public function store(PerformanceCycleRequest $request, PerformanceCycleService $service): JsonResponse { Gate::authorize('create', PerformanceCycle::class); return PerformanceCycleResource::make($service->create($request->user(), $request->validated()))->response()->setStatusCode(201); }
    public function show(PerformanceCycle $cycle): PerformanceCycleResource { Gate::authorize('view', $cycle); return PerformanceCycleResource::make($cycle->load('configuration')); }
    public function update(PerformanceCycleRequest $request, PerformanceCycle $cycle, PerformanceCycleService $service): PerformanceCycleResource { Gate::authorize('update', $cycle); return PerformanceCycleResource::make($service->update($request->user(), $cycle, $request->validated())); }
    public function configure(PerformanceCycleConfigurationRequest $request, PerformanceCycle $cycle, PerformanceCycleService $service): PerformanceCycleResource { Gate::authorize('update', $cycle); return PerformanceCycleResource::make($service->configure($request->user(), $cycle, $request->validated())); }
    public function publish(Request $request, PerformanceCycle $cycle, PerformanceCycleService $service): PerformanceCycleResource { Gate::authorize('publish', $cycle); return PerformanceCycleResource::make($service->transition($request->user(), $cycle, 'open')); }
    public function transition(Request $request, PerformanceCycle $cycle, PerformanceCycleService $service): PerformanceCycleResource { Gate::authorize('update', $cycle); $data = $request->validate(['status' => ['required', 'string']]); return PerformanceCycleResource::make($service->transition($request->user(), $cycle, $data['status'])); }
}
