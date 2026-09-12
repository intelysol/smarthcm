<?php

namespace App\Domains\Recruitment\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmRecruitmentCandidateMessage extends Model
{
    use HasUuids;

    protected $table = 'hcm_recruitment_candidate_messages';

    protected $fillable = [
        'tenant_id',
        'candidate_id',
        'application_id',
        'sender_user_id',
        'channel',
        'subject',
        'message_body',
        'is_internal_only',
    ];

    protected $casts = [
        'is_internal_only' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(HcmRecruitmentCandidate::class, 'candidate_id');
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(HcmRecruitmentApplication::class, 'application_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_user_id');
    }
}
