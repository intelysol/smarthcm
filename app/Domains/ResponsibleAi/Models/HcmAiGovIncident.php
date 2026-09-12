<?php

namespace App\Domains\ResponsibleAi\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HcmAiGovIncident extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'hcm_ai_gov_incidents';

    protected $fillable = [
        'tenant_id',
        'incident_code',
        'incident_type',
        'severity',
        'summary',
        'evidence_payload',
        'status',
        'containment_action',
        'assigned_user_id',
        'contained_at',
        'resolved_at',
    ];

    protected $casts = [
        'evidence_payload' => 'array',
        'contained_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];
}
