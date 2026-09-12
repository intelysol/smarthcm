<?php

namespace App\Domains\Expenses\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TravelRequestSegment extends Model
{
    use HasUuids;

    protected $table = 'travel_request_segments';

    protected $fillable = [
        'tenant_id',
        'travel_request_id',
        'segment_order',
        'origin',
        'destination',
        'departure_time',
        'arrival_time',
        'transport_type',
        'carrier_name',
        'booking_reference',
        'notes',
    ];

    protected $casts = [
        'segment_order' => 'integer',
        'departure_time' => 'datetime',
        'arrival_time' => 'datetime',
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
