<?php

namespace App\Domains\Offboarding\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeparationExitInterview extends Model
{
    use HasUuids;

    protected $table = 'separation_exit_interviews';

    protected $fillable = [
        'tenant_id',
        'separation_request_id',
        'interviewer_id',
        'responses',
        'is_anonymous',
        'overall_sentiment',
        'primary_reason_category',
        'confidential_notes',
        'completed_at',
    ];

    protected $casts = [
        'responses' => 'array',
        'is_anonymous' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(SeparationRequest::class, 'separation_request_id');
    }

    public function interviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'interviewer_id');
    }
}
