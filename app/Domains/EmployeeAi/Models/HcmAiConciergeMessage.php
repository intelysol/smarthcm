<?php

namespace App\Domains\EmployeeAi\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HcmAiConciergeMessage extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'hcm_ai_concierge_messages';

    protected $fillable = [
        'tenant_id',
        'session_id',
        'role',
        'content',
        'response_metadata',
        'citations',
        'action_id',
    ];

    protected $casts = [
        'response_metadata' => 'array',
        'citations' => 'array',
    ];

    public function session()
    {
        return $this->belongsTo(HcmAiConciergeSession::class, 'session_id');
    }

    public function action()
    {
        return $this->belongsTo(HcmAiConciergeAction::class, 'action_id');
    }
}
