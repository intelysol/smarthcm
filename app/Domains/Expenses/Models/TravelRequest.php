<?php

namespace App\Domains\Expenses\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class TravelRequest extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'travel_requests';

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'request_number',
        'travel_type',
        'destination',
        'purpose',
        'start_date',
        'end_date',
        'estimated_cost',
        'currency',
        'business_justification',
        'project_id',
        'cost_center_id',
        'per_diem',
        'advance_required',
        'status',
        'approved_by',
        'approved_at',
        'workflow_instance_id',
        'itinerary',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'estimated_cost' => 'decimal:4',
        'per_diem' => 'decimal:4',
        'advance_required' => 'boolean',
        'approved_at' => 'datetime',
        'itinerary' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function segments(): HasMany
    {
        return $this->hasMany(TravelRequestSegment::class)->orderBy('segment_order');
    }

    public function authorization(): HasOne
    {
        return $this->hasOne(TravelAuthorization::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(TravelBooking::class);
    }

    public function advance(): HasOne
    {
        return $this->hasOne(TravelAdvance::class);
    }

    public function claims(): HasMany
    {
        return $this->hasMany(ExpenseClaim::class);
    }
}
