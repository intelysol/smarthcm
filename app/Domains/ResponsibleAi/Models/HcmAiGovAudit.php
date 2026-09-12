<?php

namespace App\Domains\ResponsibleAi\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HcmAiGovAudit extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'hcm_ai_gov_audits';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'event_type',
        'target_type',
        'target_id',
        'summary',
        'details',
        'performed_at',
    ];

    protected $casts = [
        'details' => 'array',
        'performed_at' => 'datetime',
    ];
}
