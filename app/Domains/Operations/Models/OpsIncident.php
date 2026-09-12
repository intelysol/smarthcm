<?php
namespace App\Domains\Operations\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids; use Illuminate\Database\Eloquent\Model;
class OpsIncident extends Model { use HasUuids; protected $table = 'ops_incidents'; protected $fillable = ['tenant_id','title','description','severity','status','assignee_id','timeline','root_cause','resolved_at']; protected function casts(): array { return ['timeline'=>'array','resolved_at'=>'datetime']; } }
