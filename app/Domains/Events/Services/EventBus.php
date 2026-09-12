<?php
namespace App\Domains\Events\Services;
use App\Domains\Events\Models\StoredEvent; use Illuminate\Support\Str;
class EventBus { public function publish(array $data):StoredEvent{return StoredEvent::query()->create([...$data,'id'=>$data['id']??(string)Str::uuid(),'environment'=>$data['environment']??app()->environment(),'occurred_at'=>$data['occurred_at']??now()]);} public function replay(StoredEvent $event):StoredEvent{return $this->publish([...$event->only(['tenant_id','event_type','event_version','aggregate_type','aggregate_id','correlation_id','causation_id','actor_type','actor_id','source_module','environment','payload','tags','signature']),'causation_id'=>$event->id]);} }
