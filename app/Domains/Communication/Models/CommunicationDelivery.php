<?php
namespace App\Domains\Communication\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids; use Illuminate\Database\Eloquent\Model;
class CommunicationDelivery extends Model { use HasUuids; protected $table='communication_deliveries'; protected $fillable=['tenant_id','user_id','template_id','channel','notification_type','recipient','subject','body','status','attempts','provider','error_message','scheduled_at','sent_at','delivered_at','read_at']; protected function casts():array{return ['scheduled_at'=>'datetime','sent_at'=>'datetime','delivered_at'=>'datetime','read_at'=>'datetime'];} }
