<?php

namespace App\Domains\AiOperations\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HcmAiFeedback extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'hcm_ai_feedback';

    protected $fillable = [
        'tenant_id',
        'telemetry_id',
        'message_id',
        'user_id',
        'rating',
        'is_positive',
        'feedback_category',
        'task_completed',
        'comment',
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_positive' => 'boolean',
        'task_completed' => 'boolean',
    ];

    public function telemetry()
    {
        return $this->belongsTo(HcmAiInteractionTelemetry::class, 'telemetry_id');
    }
}
