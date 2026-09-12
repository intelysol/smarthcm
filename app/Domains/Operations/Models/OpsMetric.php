<?php
namespace App\Domains\Operations\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids; use Illuminate\Database\Eloquent\Model;
class OpsMetric extends Model { use HasUuids; protected $table = 'ops_metrics'; protected $fillable = ['tenant_id','metric','value','unit','dimensions','recorded_at']; protected function casts(): array { return ['value'=>'float','dimensions'=>'array','recorded_at'=>'datetime']; } }
