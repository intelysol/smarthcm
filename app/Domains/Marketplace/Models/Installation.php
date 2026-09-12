<?php
namespace App\Domains\Marketplace\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids; use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Installation extends Model { use HasUuids; protected $table='marketplace_installations'; protected $fillable=['tenant_id','extension_id','version_id','status','installed_at']; protected function casts():array{return ['installed_at'=>'datetime'];} public function extension():BelongsTo{return $this->belongsTo(Extension::class,'extension_id');} public function version():BelongsTo{return $this->belongsTo(ExtensionVersion::class,'version_id');} }
