<?php

declare(strict_types=1);

namespace Flow\Packages\Billing\Domain\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillingUsageEvent extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $table = 'billing_usage_events';

    protected $fillable = [
        'id',
        'tenant_id',
        'meter_key',
        'quantity',
        'recorded_at',
        'idempotency_key',
        'source',
        'metadata',
        'created_at',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'recorded_at' => 'datetime',
        'created_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }
}
