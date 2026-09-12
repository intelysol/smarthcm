<?php

namespace App\Domains\WorkforceAdmin\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpsExceptionEvent extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $table = 'hcm_ops_exception_events';

    protected $fillable = [
        'tenant_id',
        'exception_id',
        'event_type',
        'from_status',
        'to_status',
        'comment',
        'actor_id',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function exception(): BelongsTo
    {
        return $this->belongsTo(OpsException::class, 'exception_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
