<?php

namespace App\Domains\Expenses\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TravelAuthorization extends Model
{
    use HasUuids;

    protected $table = 'travel_authorizations';

    protected $fillable = [
        'tenant_id',
        'travel_request_id',
        'authorization_number',
        'approved_budget',
        'currency',
        'valid_from',
        'valid_to',
        'approved_by',
        'approved_at',
        'status',
        'notes',
    ];

    protected $casts = [
        'approved_budget' => 'decimal:4',
        'valid_from' => 'date',
        'valid_to' => 'date',
        'approved_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function travelRequest(): BelongsTo
    {
        return $this->belongsTo(TravelRequest::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function claims(): HasMany
    {
        return $this->hasMany(ExpenseClaim::class);
    }
}
