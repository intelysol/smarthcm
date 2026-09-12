<?php
namespace App\Domains\Operations\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids; use Illuminate\Database\Eloquent\Model;
class OpsAlert extends Model { use HasUuids; protected $table = 'ops_alerts'; protected $fillable = ['tenant_id','rule_id','severity','status','message','payload','triggered_at','resolved_at']; protected function casts(): array { return ['payload'=>'array','triggered_at'=>'datetime','resolved_at'=>'datetime']; } }
