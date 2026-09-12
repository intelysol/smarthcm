<?php

namespace App\Domains\Benefits\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanProductVersion extends Model
{
    use HasUuids;

    protected $table = 'loan_product_versions';

    protected $fillable = [
        'tenant_id',
        'loan_product_id',
        'version_number',
        'effective_from',
        'effective_to',
        'interest_rate_annual',
        'interest_method',
        'maximum_amount',
        'is_active',
    ];

    protected $casts = [
        'version_number' => 'integer',
        'effective_from' => 'date',
        'effective_to' => 'date',
        'interest_rate_annual' => 'decimal:4',
        'maximum_amount' => 'decimal:4',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(LoanProduct::class, 'loan_product_id');
    }
}
