<?php

namespace App\Domains\SelfService\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrServiceSlaPolicy extends Model
{
    use HasUuids;

    protected $table = 'hr_service_sla_policies';

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'description',
        'response_time_minutes',
        'resolution_time_minutes',
        'use_business_hours',
        'business_calendar_code',
        'is_active',
    ];

    protected $casts = [
        'response_time_minutes' => 'integer',
        'resolution_time_minutes' => 'integer',
        'use_business_hours' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
