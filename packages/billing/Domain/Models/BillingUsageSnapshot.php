<?php

declare(strict_types=1);

namespace Flow\Packages\Billing\Domain\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillingUsageSnapshot extends Model
{
    use HasUuids;

    protected $table = 'billing_usage_snapshots';

    protected $fillable = [
        'id',
        'tenant_id',
        'meter_key',
        'period_start',
        'period_end',
        'total_quantity',
        'billable_quantity',
        'snapshotted_at',
    ];

    protected $casts = [
        'period_start' => 'datetime',
        'period_end' => 'datetime',
        'snapshotted_at' => 'datetime',
        'total_quantity' => 'decimal:4',
        'billable_quantity' => 'decimal:4',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }
}
