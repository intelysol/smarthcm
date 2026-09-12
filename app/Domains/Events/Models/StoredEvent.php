<?php
namespace App\Domains\Events\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids; use Illuminate\Database\Eloquent\Model;
class StoredEvent extends Model { use HasUuids; protected $table='event_store'; protected $fillable=['tenant_id','event_type','event_version','aggregate_type','aggregate_id','correlation_id','causation_id','actor_type','actor_id','source_module','environment','payload','tags','signature','occurred_at']; protected function casts():array{return ['payload'=>'array','tags'=>'array','occurred_at'=>'datetime'];} }
