<?php

declare(strict_types=1);

namespace Flow\Packages\Billing\Domain\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BillingCredit extends Model
{
    use HasUuids;

    protected $table = 'billing_credits';

    protected $fillable = [
        'id',
        'tenant_id',
        'amount',
        'balance',
        'currency',
        'reason',
        'status',
        'expires_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance' => 'decimal:2',
        'expires_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(BillingCreditTransaction::class, 'credit_id');
    }
}
