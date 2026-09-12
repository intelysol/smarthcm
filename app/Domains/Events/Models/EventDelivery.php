<?php
namespace App\Domains\Events\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids; use Illuminate\Database\Eloquent\Model;
class EventDelivery extends Model { use HasUuids; protected $table='event_deliveries'; protected $fillable=['event_id','consumer','status','attempts','error_message','next_attempt_at','processed_at']; protected function casts():array{return ['next_attempt_at'=>'datetime','processed_at'=>'datetime'];} }
