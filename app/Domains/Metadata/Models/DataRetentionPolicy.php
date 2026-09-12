<?php
namespace App\Domains\Metadata\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids; use Illuminate\Database\Eloquent\Model;
class DataRetentionPolicy extends Model { use HasUuids; protected $table='data_retention_policies'; protected $fillable=['tenant_id','table_name','retention_days','archive_strategy','legal_hold_supported']; protected function casts():array{return ['legal_hold_supported'=>'boolean'];} }
