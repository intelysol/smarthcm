<?php

declare(strict_types=1);

namespace App\Domains\Compliance\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PrivacyProcessingActivity extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'privacy_processing_activities';

    protected $fillable = [
        'tenant_id',
        'name',
        'purpose',
        'business_owner',
        'data_categories',
        'legal_basis',
        'processing_location',
        'retention_policy_ref',
    ];

    protected function casts(): array
    {
        return [
            'data_categories' => 'array',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function impactAssessments(): HasMany
    {
        return $this->hasMany(PrivacyImpactAssessment::class, 'processing_activity_id');
    }
}
