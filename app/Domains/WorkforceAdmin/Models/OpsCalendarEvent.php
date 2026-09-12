<?php

namespace App\Domains\WorkforceAdmin\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpsCalendarEvent extends Model
{
    use HasUuids;

    protected $table = 'hcm_ops_calendar_events';

    protected $fillable = [
        'tenant_id',
        'event_type',
        'title',
        'event_date',
        'employee_id',
        'source_domain',
        'source_entity_type',
        'source_entity_id',
        'metadata',
    ];

    protected $casts = [
        'event_date' => 'date',
        'metadata' => 'array',
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
