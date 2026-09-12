<?php

namespace App\Domains\SelfService\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrServiceSlaEvent extends Model
{
    use HasUuids;

    protected $table = 'hr_service_sla_events';

    protected $fillable = [
        'tenant_id',
        'hr_service_sla_instance_id',
        'event_type',
        'event_at',
        'details',
    ];

    protected $casts = [
        'event_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function instance(): BelongsTo
    {
        return $this->belongsTo(HrServiceSlaInstance::class, 'hr_service_sla_instance_id');
    }
}
