<?php

namespace App\Domains\EmployeeAi\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HcmAiConciergeAction extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'hcm_ai_concierge_actions';

    protected $fillable = [
        'tenant_id',
        'session_id',
        'user_id',
        'employee_id',
        'action_type',
        'risk_level',
        'parameters',
        'preview_data',
        'status',
        'workflow_reference_id',
        'user_comment',
        'confirmed_at',
    ];

    protected $casts = [
        'parameters' => 'array',
        'preview_data' => 'array',
        'confirmed_at' => 'datetime',
    ];
}
