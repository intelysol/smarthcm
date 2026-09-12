<?php
namespace App\Domains\Communication\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids; use Illuminate\Database\Eloquent\Model;
class CommunicationPreference extends Model { use HasUuids; protected $table='communication_preferences'; protected $fillable=['tenant_id','user_id','channels','locale','timezone','quiet_start','quiet_end']; protected function casts():array{return ['channels'=>'array'];} }
