<?php

namespace App\Domains\Recruitment\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmRecruitmentOfferApproval extends Model
{
    use HasUuids;

    protected $table = 'hcm_recruitment_offer_approvals';

    protected $fillable = [
        'tenant_id',
        'offer_id',
        'approver_id',
        'role',
        'status',
        'comments',
        'acted_at',
    ];

    protected $casts = [
        'acted_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function offer(): BelongsTo
    {
        return $this->belongsTo(HcmRecruitmentOffer::class, 'offer_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
