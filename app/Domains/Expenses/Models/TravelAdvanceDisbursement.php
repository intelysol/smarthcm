<?php

namespace App\Domains\Expenses\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TravelAdvanceDisbursement extends Model
{
    use HasUuids;

    protected $table = 'travel_advance_disbursements';

    protected $fillable = [
        'tenant_id',
        'travel_advance_id',
        'disbursement_number',
        'disbursed_amount',
        'currency',
        'disbursement_date',
        'payment_method',
        'finance_reference',
        'disbursed_by',
        'notes',
    ];

    protected $casts = [
        'disbursed_amount' => 'decimal:4',
        'disbursement_date' => 'date',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function advance(): BelongsTo
    {
        return $this->belongsTo(TravelAdvance::class, 'travel_advance_id');
    }

    public function disburser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disbursed_by');
    }
}
