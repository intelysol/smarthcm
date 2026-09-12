<?php
namespace App\Domains\Api\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids; use Illuminate\Database\Eloquent\Model;
class ApiCatalogEntry extends Model { use HasUuids; protected $table='api_catalog'; protected $fillable=['version','method','path','name','description','request_schema','response_schema','security','status']; protected function casts():array{return ['request_schema'=>'array','response_schema'=>'array','security'=>'array'];} }
