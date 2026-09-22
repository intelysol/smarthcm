<?php

namespace App\Domains\AiOperations\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HcmAiImprovementItem extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'hcm_ai_improvement_items';

    protected $fillable = [
        'tenant_id',
        'item_code',
        'problem',
        'source',
        'severity',
        'frequency_count',
        'affected_use_cases',
        'evidence_summary',
        'recommended_action',
        'owner_user_id',
        'status',
    ];

    protected $casts = [
        'frequency_count' => 'integer',
        'affected_use_cases' => 'array',
    ];
}
