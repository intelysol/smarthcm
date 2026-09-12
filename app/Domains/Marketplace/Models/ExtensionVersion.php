<?php
namespace App\Domains\Marketplace\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids; use Illuminate\Database\Eloquent\Model;
class ExtensionVersion extends Model { use HasUuids; protected $table='marketplace_versions'; protected $fillable=['extension_id','version','platform_constraint','dependencies','artifact_path','checksum','signature','status']; protected function casts():array{return ['dependencies'=>'array'];} }
