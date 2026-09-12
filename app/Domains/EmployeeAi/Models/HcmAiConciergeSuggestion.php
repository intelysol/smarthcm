<?php

namespace App\Domains\EmployeeAi\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HcmAiConciergeSuggestion extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'hcm_ai_concierge_suggestions';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'employee_id',
        'category',
        'title',
        'description',
        'action_label',
        'action_prompt',
        'severity',
        'expires_at',
        'is_dismissed',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_dismissed' => 'boolean',
    ];
}
