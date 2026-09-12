<?php

namespace App\Domains\HealthSafety\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmSafetyIncidentWitness extends Model
{
    use HasUuids;

    protected $table = 'hcm_safety_incident_witnesses';

    protected $fillable = [
        'tenant_id',
        'safety_incident_id',
        'employee_id',
        'witness_name',
        'contact_phone',
        'statement',
        'statement_date',
    ];

    protected $casts = [
        'statement_date' => 'date',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function incident(): BelongsTo
    {
        return $this->belongsTo(HcmSafetyIncident::class, 'safety_incident_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
