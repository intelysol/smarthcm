<?php

namespace App\Domains\Offboarding\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeparationNoticePeriod extends Model
{
    use HasUuids;

    protected $table = 'separation_notice_periods';

    protected $fillable = [
        'tenant_id',
        'separation_request_id',
        'notice_start_date',
        'required_days',
        'calculated_last_working_day',
        'agreed_days',
        'adjusted_last_working_day',
        'is_overridden',
        'override_reason',
        'overridden_by',
        'is_waived',
        'is_buyout',
        'buyout_amount',
    ];

    protected $casts = [
        'notice_start_date' => 'date',
        'calculated_last_working_day' => 'date',
        'adjusted_last_working_day' => 'date',
        'required_days' => 'integer',
        'agreed_days' => 'integer',
        'is_overridden' => 'boolean',
        'is_waived' => 'boolean',
        'is_buyout' => 'boolean',
        'buyout_amount' => 'decimal:2',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(SeparationRequest::class, 'separation_request_id');
    }

    public function overrider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'overridden_by');
    }
}
