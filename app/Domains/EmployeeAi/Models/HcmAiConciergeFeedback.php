<?php

namespace App\Domains\EmployeeAi\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HcmAiConciergeFeedback extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'hcm_ai_concierge_feedback';

    protected $fillable = [
        'tenant_id',
        'message_id',
        'user_id',
        'is_positive',
        'reason_category',
        'comments',
    ];

    protected $casts = [
        'is_positive' => 'boolean',
    ];
}
