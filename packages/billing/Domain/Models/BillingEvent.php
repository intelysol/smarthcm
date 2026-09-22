<?php

declare(strict_types=1);

namespace Flow\Packages\Billing\Domain\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillingEvent extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $table = 'billing_events';

    protected $fillable = [
        'id',
        'tenant_id',
        'event_type',
        'payload',
        'actor_id',
        'ip_address',
        'created_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'actor_id' => 'integer',
        'created_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
