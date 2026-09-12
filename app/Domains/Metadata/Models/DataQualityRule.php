<?php
namespace App\Domains\Metadata\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids; use Illuminate\Database\Eloquent\Model;
class DataQualityRule extends Model { use HasUuids; protected $table='data_quality_rules'; protected $fillable=['tenant_id','name','table_name','definition','is_active']; protected function casts():array{return ['definition'=>'array','is_active'=>'boolean'];} }
