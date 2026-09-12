<?php

namespace App\Domains\WorkforceIntelligence\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommandCenterDecisionItem extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'hcm_command_center_decision_items';

    protected $fillable = [
        'tenant_id',
        'department_id',
        'decision_code',
        'source_domain',
        'source_reference_id',
        'title',
        'summary',
        'urgency',
        'estimated_cost_impact',
        'estimated_capacity_impact',
        'status',
        'assigned_approver_id',
        'actioned_by_user_id',
        'action_notes',
        'actioned_at',
        'deadline_at',
        'payload',
    ];

    protected $casts = [
        'estimated_cost_impact' => 'decimal:2',
        'estimated_capacity_impact' => 'decimal:2',
        'actioned_at' => 'datetime',
        'deadline_at' => 'datetime',
        'payload' => 'array',
    ];
}
