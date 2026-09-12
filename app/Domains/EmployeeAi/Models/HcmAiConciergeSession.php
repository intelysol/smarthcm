<?php

namespace App\Domains\EmployeeAi\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HcmAiConciergeSession extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'hcm_ai_concierge_sessions';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'employee_id',
        'persona',
        'title',
        'status',
        'session_metadata',
        'last_active_at',
    ];

    protected $casts = [
        'session_metadata' => 'array',
        'last_active_at' => 'datetime',
    ];

    public function messages()
    {
        return $this->hasMany(HcmAiConciergeMessage::class, 'session_id');
    }
}
