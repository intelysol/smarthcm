<?php

namespace App\Domains\Expenses\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TravelAdvance extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'travel_advances';

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'travel_request_id',
        'advance_number',
        'requested_amount',
        'approved_amount',
        'disbursed_amount',
        'settled_amount',
        'currency',
        'purpose',
        'status',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'requested_amount' => 'decimal:4',
        'approved_amount' => 'decimal:4',
        'disbursed_amount' => 'decimal:4',
        'settled_amount' => 'decimal:4',
        'approved_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function travelRequest(): BelongsTo
    {
        return $this->belongsTo(TravelRequest::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function disbursements(): HasMany
    {
        return $this->hasMany(TravelAdvanceDisbursement::class);
    }

    public function settlements(): HasMany
    {
        return $this->hasMany(TravelAdvanceSettlement::class);
    }

    public function remainingUnsettledAmount(): float
    {
        return max(0.0, (float) $this->disbursed_amount - (float) $this->settled_amount);
    }
}
