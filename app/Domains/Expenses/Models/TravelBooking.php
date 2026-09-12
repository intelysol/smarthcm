<?php

namespace App\Domains\Expenses\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TravelBooking extends Model
{
    use HasUuids;

    protected $table = 'travel_bookings';

    protected $fillable = [
        'tenant_id',
        'travel_request_id',
        'booking_type',
        'provider_name',
        'booking_reference',
        'confirmation_number',
        'start_date',
        'end_date',
        'cost',
        'currency',
        'status',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'cost' => 'decimal:4',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function travelRequest(): BelongsTo
    {
        return $this->belongsTo(TravelRequest::class);
    }
}
