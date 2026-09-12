<?php

namespace App\Domains\SelfService\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ProfileChangeRequest extends Model
{
    use HasUuids;
    protected $fillable = ['tenant_id','employee_id','requested_by','request_type','requested_changes','reason','status','reviewed_by','reviewed_at','effective_at'];
    protected function casts(): array { return ['requested_changes' => 'array','reviewed_at' => 'datetime','effective_at' => 'datetime']; }
}
