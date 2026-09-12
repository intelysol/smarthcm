<?php

namespace App\Domains\Recruitment\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmRecruitmentOfferVersion extends Model
{
    use HasUuids;

    protected $table = 'hcm_recruitment_offer_versions';

    protected $fillable = [
        'tenant_id',
        'offer_id',
        'version_number',
        'base_salary',
        'bonus_amount',
        'start_date',
        'expiry_date',
        'change_rationale',
        'created_by',
    ];

    protected $casts = [
        'version_number' => 'integer',
        'base_salary' => 'decimal:2',
        'bonus_amount' => 'decimal:2',
        'start_date' => 'date',
        'expiry_date' => 'date',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function offer(): BelongsTo
    {
        return $this->belongsTo(HcmRecruitmentOffer::class, 'offer_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
