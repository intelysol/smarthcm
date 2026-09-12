<?php

namespace App\Domains\Mobility\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MobilityBusinessTraveler extends Model
{
    use HasUuids;

    protected $table = 'hcm_mobility_business_travelers';

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'traveler_trip_number',
        'destination_country',
        'destination_city',
        'start_date',
        'end_date',
        'trip_days',
        'business_purpose',
        'compliance_risk_level',
        'visa_required',
        'visa_cleared',
        'expense_travel_request_id',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'trip_days' => 'integer',
        'visa_required' => 'boolean',
        'visa_cleared' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
