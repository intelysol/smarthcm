<?php

namespace App\Domains\Rules\Http\Controllers;

use App\Domains\Platform\Contracts\TenantContext;
use App\Domains\Rules\DTOs\RuleExecutionContext;
use App\Domains\Rules\Repositories\BusinessRuleRepository;
use App\Domains\Rules\Requests\StoreBusinessRuleRequest;
use App\Domains\Rules\Resources\BusinessRuleResource;
use App\Domains\Rules\Services\RuleEngine;
use App\Domains\Rules\Services\RuleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Domains\Rules\Models\RuleExecution;

class BusinessRuleController
{
    public function __construct(private readonly BusinessRuleRepository $rules, private readonly RuleService $service, private readonly RuleEngine $engine, private readonly TenantContext $tenant) {}
    public function index(Request $request): JsonResponse { $this->allow($request, 'rules.view'); return BusinessRuleResource::collection($this->rules->paginate($this->tenant->id(), $request->query()))->response(); }
    public function store(StoreBusinessRuleRequest $request): JsonResponse { return BusinessRuleResource::make($this->service->create($this->tenant->id(), $request->user(), $request->validated()))->response()->setStatusCode(201); }
    public function show(Request $request, string $rule): BusinessRuleResource { $this->allow($request, 'rules.view'); return BusinessRuleResource::make($this->rules->find($this->tenant->id(), $rule)); }
    public function transition(Request $request, string $rule): JsonResponse { $model = $this->rules->find($this->tenant->id(), $rule); $this->allow($request, 'rules.manage'); return BusinessRuleResource::make($this->service->transition($model, $request->user(), $request->validate(['status' => ['required', 'in:under_review,approved,published,deprecated,archived,draft']])['status']))->response(); }
    public function test(Request $request, string $rule): JsonResponse { $model = $this->rules->find($this->tenant->id(), $rule); $this->allow($request, 'rules.execute'); $data = $request->validate(['data' => ['required', 'array'], 'variables' => ['nullable', 'array']]); return response()->json(['data' => $this->engine->execute($model, new RuleExecutionContext($this->tenant->id(), $data['data'], $data['variables'] ?? []), true)]); }
    public function execute(Request $request, string $rule): JsonResponse { $model = $this->rules->find($this->tenant->id(), $rule); $this->allow($request, 'rules.execute'); abort_unless($model->status === 'published', 422, 'Only published rules can execute.'); $data = $request->validate(['data' => ['required', 'array'], 'variables' => ['nullable', 'array'], 'subject_type' => ['nullable', 'string'], 'subject_id' => ['nullable', 'string']]); return response()->json(['data' => $this->engine->execute($model, new RuleExecutionContext($this->tenant->id(), $data['data'], $data['variables'] ?? [], $data['subject_type'] ?? null, $data['subject_id'] ?? null, $request->header('X-Correlation-ID')))]); }
    public function versions(Request $request, string $rule): JsonResponse { $model = $this->rules->find($this->tenant->id(), $rule); $this->allow($request, 'rules.view'); return response()->json(['data' => $model->newQuery()->where('tenant_id', $this->tenant->id())->where('key', $model->key)->orderByDesc('version')->get()->map(fn ($item) => BusinessRuleResource::make($item)->resolve($request))]); }
    public function cloneVersion(Request $request, string $rule): JsonResponse { $model = $this->rules->find($this->tenant->id(), $rule); $this->allow($request, 'rules.manage'); return BusinessRuleResource::make($this->service->version($model, $request->user()))->response()->setStatusCode(201); }
    public function rollback(Request $request, string $rule): JsonResponse { $model = $this->rules->find($this->tenant->id(), $rule); $this->allow($request, 'rules.manage'); $data = $request->validate(['version' => ['required', 'integer', 'min:1']]); $target = $model->newQuery()->where('tenant_id', $this->tenant->id())->where('key', $model->key)->where('version', $data['version'])->firstOrFail(); return BusinessRuleResource::make($this->service->rollback($model, $target, $request->user()))->response()->setStatusCode(201); }
    public function executions(Request $request, string $rule): JsonResponse { $model = $this->rules->find($this->tenant->id(), $rule); $this->allow($request, 'rules.view'); return response()->json(['data' => RuleExecution::query()->where('tenant_id', $this->tenant->id())->where('business_rule_id', $model->id)->latest('executed_at')->paginate(min(max((int) $request->integer('per_page', 25), 1), 100))]); }
    private function allow(Request $request, string $permission): void { $request->user()->hasPermission($permission) || abort(403); }
}
