<?php
namespace App\Domains\Operations\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids; use Illuminate\Database\Eloquent\Model;
class OpsAlertRule extends Model { use HasUuids; protected $table = 'ops_alert_rules'; protected $fillable = ['tenant_id','name','metric','operator','threshold','severity','channels','is_active']; protected function casts(): array { return ['threshold'=>'float','channels'=>'array','is_active'=>'boolean']; } }
