<?php

declare(strict_types=1);

namespace App\Domains\Compliance\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrivacyImpactAssessment extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'privacy_impact_assessments';

    protected $fillable = [
        'tenant_id',
        'processing_activity_id',
        'title',
        'necessity_summary',
        'risk_level',
        'mitigations',
        'dpo_approval',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'mitigations' => 'array',
            'dpo_approval' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function processingActivity(): BelongsTo
    {
        return $this->belongsTo(PrivacyProcessingActivity::class, 'processing_activity_id');
    }
}
