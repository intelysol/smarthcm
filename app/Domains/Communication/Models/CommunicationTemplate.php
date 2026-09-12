<?php
namespace App\Domains\Communication\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids; use Illuminate\Database\Eloquent\Model;
class CommunicationTemplate extends Model { use HasUuids; protected $table='communication_templates'; protected $fillable=['tenant_id','key','name','channel','locale','subject','body','version','status']; }
