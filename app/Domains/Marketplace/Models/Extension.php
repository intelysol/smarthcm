<?php
namespace App\Domains\Marketplace\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids; use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\Relations\HasMany;
class Extension extends Model { use HasUuids; protected $table='marketplace_extensions'; protected $fillable=['slug','name','publisher','extension_type','description','license','status','manifest','security']; protected function casts():array{return ['manifest'=>'array','security'=>'array'];} public function versions():HasMany{return $this->hasMany(ExtensionVersion::class,'extension_id');} }
