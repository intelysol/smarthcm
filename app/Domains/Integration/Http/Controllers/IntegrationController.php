<?php
namespace App\Domains\Integration\Http\Controllers;
use App\Domains\Integration\Models\{IntegrationConnection, IntegrationConnector, IntegrationEvent, WebhookSubscription};
use App\Domains\Integration\Services\IntegrationService;
use App\Domains\Platform\Contracts\TenantContext;
use Illuminate\Http\{JsonResponse, Request};
class IntegrationController
{
    public function __construct(private readonly IntegrationService $service, private readonly TenantContext $tenant) {}
    public function connectors(Request $request): JsonResponse { $this->allow($request, 'integrations.view'); return response()->json(['data' => IntegrationConnector::query()->where(fn ($q) => $q->whereNull('tenant_id')->orWhere('tenant_id', $this->tenant->id()))->where('status', '!=', 'disabled')->get()]); }
    public function connections(Request $request): JsonResponse { $this->allow($request, 'integrations.view'); return response()->json(['data' => IntegrationConnection::query()->where('tenant_id', $this->tenant->id())->with('connector')->paginate(25)]); }
    public function createConnection(Request $request): JsonResponse { $this->allow($request, 'integrations.manage'); $data = $request->validate(['connector_id' => ['required', 'uuid'], 'name' => ['required', 'string', 'max:160'], 'configuration' => ['nullable', 'array'], 'encrypted_credentials' => ['nullable', 'array']]); abort_unless(IntegrationConnector::query()->where(fn ($q) => $q->whereNull('tenant_id')->orWhere('tenant_id', $this->tenant->id()))->whereKey($data['connector_id'])->exists(), 422, 'Connector is unavailable.'); return response()->json(['data' => $this->service->connection($this->tenant->id(), $data)], 201); }
    public function publish(Request $request): JsonResponse { $this->allow($request, 'integrations.execute'); $data = $request->validate(['event_type' => ['required', 'string', 'max:100'], 'payload' => ['required', 'array'], 'subject_type' => ['nullable', 'string'], 'subject_id' => ['nullable', 'string']]); return response()->json(['data' => $this->service->publish($this->tenant->id(), $data['event_type'], $data['payload'], $data['subject_type'] ?? null, $data['subject_id'] ?? null)], 201); }
    public function webhooks(Request $request): JsonResponse { $this->allow($request, 'integrations.manage'); $data = $request->validate(['url' => ['required', 'url'], 'event_filters' => ['nullable', 'array'], 'secret_reference' => ['nullable', 'string', 'max:255'], 'max_retries' => ['sometimes', 'integer', 'min:0', 'max:20']]); return response()->json(['data' => WebhookSubscription::query()->create([...$data, 'tenant_id' => $this->tenant->id()])], 201); }
    public function events(Request $request): JsonResponse { $this->allow($request, 'integrations.view'); return response()->json(['data' => IntegrationEvent::query()->where('tenant_id', $this->tenant->id())->latest('occurred_at')->paginate(25)]); }
    private function allow(Request $request, string $permission): void { $request->user()->hasPermission($permission) || abort(403); }
}
