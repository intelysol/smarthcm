<?php

namespace App\Domains\Attendance\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmOpenShiftBid extends Model
{
    use HasUuids;

    protected $table = 'hcm_open_shift_bids';

    protected $fillable = [
        'tenant_id',
        'open_shift_id',
        'employee_id',
        'bid_status',
        'eligibility_score',
        'eligibility_details',
    ];

    protected $casts = [
        'eligibility_score' => 'decimal:2',
        'eligibility_details' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function openShift(): BelongsTo
    {
        return $this->belongsTo(HcmOpenShift::class, 'open_shift_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
