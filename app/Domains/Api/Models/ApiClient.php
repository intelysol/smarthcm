<?php
namespace App\Domains\Api\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids; use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\Relations\HasMany;
class ApiClient extends Model { use HasUuids; protected $table='api_clients'; protected $fillable=['tenant_id','name','client_type','client_identifier','encrypted_secret','scopes','ip_allowlist','status','secret_expires_at']; protected function casts():array{return ['scopes'=>'array','ip_allowlist'=>'array','secret_expires_at'=>'datetime'];} public function keys():HasMany{return $this->hasMany(ApiKey::class,'client_id');} }
