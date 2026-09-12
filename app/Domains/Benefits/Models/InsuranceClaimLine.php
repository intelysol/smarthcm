<?php

namespace App\Domains\Benefits\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InsuranceClaimLine extends Model
{
    use HasUuids;

    protected $table = 'insurance_claim_lines';

    protected $fillable = [
        'tenant_id',
        'benefit_claim_id',
        'item_description',
        'claimed_amount',
        'approved_amount',
        'invoice_number',
        'invoice_date',
    ];

    protected $casts = [
        'claimed_amount' => 'decimal:4',
        'approved_amount' => 'decimal:4',
        'invoice_date' => 'date',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function claim(): BelongsTo
    {
        return $this->belongsTo(InsuranceClaim::class, 'benefit_claim_id');
    }
}
