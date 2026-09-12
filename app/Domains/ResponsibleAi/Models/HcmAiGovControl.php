<?php

namespace App\Domains\ResponsibleAi\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HcmAiGovControl extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'hcm_ai_gov_controls';

    protected $fillable = [
        'tenant_id',
        'control_code',
        'title',
        'category',
        'description',
        'test_status',
        'last_tested_at',
        'evidence_summary',
    ];

    protected $casts = [
        'last_tested_at' => 'datetime',
    ];
}
