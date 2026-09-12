<?php

namespace App\Domains\Shared\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AuditService
{
    public function __construct(private readonly AuditRedactor $redactor) {}
    /** @param array<string,mixed>|null $before @param array<string,mixed>|null $after */
    public function record(string $tenantId, string $eventType, string $action, ?string $entityType = null, ?string $entityId = null, ?int $actorId = null, ?array $before = null, ?array $after = null): string
    {
        $previous = DB::table('audit_events')->where('tenant_id', $tenantId)->latest('occurred_at')->value('integrity_hash'); $before = $this->redactor->redact($before); $after = $this->redactor->redact($after); $occurred = now(); $hash = hash('sha256', ($previous ?? '').json_encode([$eventType,$action,$entityType,$entityId,$before,$after,$occurred->toAtomString()])); $id = (string) Str::uuid();
        DB::table('audit_events')->insert(['id' => $id,'tenant_id' => $tenantId,'event_type' => $eventType,'action' => $action,'entity_type' => $entityType,'entity_id' => $entityId,'actor_id' => $actorId,'before_data' => json_encode($before),'after_data' => json_encode($after),'previous_hash' => $previous,'integrity_hash' => $hash,'occurred_at' => $occurred,'created_at' => $occurred,'updated_at' => $occurred]); return $id;
    }
    public function verify(string $tenantId): bool { $previous = null; foreach (DB::table('audit_events')->where('tenant_id',$tenantId)->orderBy('occurred_at')->get() as $event) { $hash = hash('sha256', ($previous ?? '').json_encode([$event->event_type,$event->action,$event->entity_type,$event->entity_id,json_decode($event->before_data,true),json_decode($event->after_data,true),\Carbon\Carbon::parse($event->occurred_at)->toAtomString()])); if (!hash_equals($hash,$event->integrity_hash)) return false; $previous=$event->integrity_hash; } return true; }
}
