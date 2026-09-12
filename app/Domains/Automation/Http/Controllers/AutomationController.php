<?php
namespace App\Domains\Automation\Http\Controllers;
use App\Domains\Automation\Models\{Automation, AutomationExecution};
use App\Domains\Automation\Services\AutomationEngine;
use App\Domains\Platform\Contracts\TenantContext;
use Illuminate\Http\{JsonResponse, Request};
class AutomationController
{
    public function __construct(private readonly AutomationEngine $engine, private readonly TenantContext $tenant) {}
    public function index(Request $request): JsonResponse { $this->allow($request, 'automations.view'); return response()->json(['data' => Automation::query()->where('tenant_id', $this->tenant->id())->latest()->paginate(25)]); }
    public function store(Request $request): JsonResponse { $this->allow($request, 'automations.manage'); $data = $request->validate(['key' => ['required', 'alpha_dash', 'max:100'], 'name' => ['required', 'string', 'max:160'], 'definition' => ['required', 'array'], 'settings' => ['nullable', 'array']]); return response()->json(['data' => Automation::query()->create([...$data, 'tenant_id' => $this->tenant->id(), 'created_by' => $request->user()->id])], 201); }
    public function transition(Request $request, string $automation): JsonResponse { $this->allow($request, 'automations.manage'); $model = $this->find($automation); $status = $request->validate(['status' => ['required', 'in:draft,under_review,approved,published,deprecated,archived']])['status']; $model->update(['status' => $status]); return response()->json(['data' => $model]); }
    public function run(Request $request, string $automation): JsonResponse { $this->allow($request, 'automations.execute'); $model = $this->find($automation); abort_unless($model->status === 'published' || $request->boolean('dry_run'), 422, 'Only published automations can run.'); $data = $request->validate(['trigger' => ['sometimes', 'string', 'max:50'], 'context' => ['nullable', 'array'], 'dry_run' => ['sometimes', 'boolean']]); return response()->json(['data' => $this->engine->run($model, $this->tenant->id(), $data['trigger'] ?? 'manual', $data['context'] ?? [], (bool) ($data['dry_run'] ?? false))]); }
    public function executions(Request $request, string $automation): JsonResponse { $this->allow($request, 'automations.view'); $model = $this->find($automation); return response()->json(['data' => AutomationExecution::query()->where('tenant_id', $this->tenant->id())->where('automation_id', $model->id)->with('steps')->latest()->paginate(25)]); }
    private function find(string $id): Automation { return Automation::query()->where('tenant_id', $this->tenant->id())->findOrFail($id); }
    private function allow(Request $request, string $permission): void { $request->user()->hasPermission($permission) || abort(403); }
}
