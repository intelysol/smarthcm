<?php

namespace App\Domains\Mobility\Models;

use App\Domains\Compliance\Models\HcmEmployeeVisaRecord;
use App\Domains\Compliance\Models\HcmEmployeeWorkPermit;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MobilityComplianceLink extends Model
{
    use HasUuids;

    protected $table = 'hcm_mobility_compliance_links';

    protected $fillable = [
        'tenant_id',
        'assignment_id',
        'compliance_type',
        'compliance_record_id',
        'compliance_status',
        'valid_until',
        'notes',
    ];

    protected $casts = [
        'valid_until' => 'date',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(MobilityAssignment::class, 'assignment_id');
    }

    public function visaRecord(): BelongsTo
    {
        return $this->belongsTo(HcmEmployeeVisaRecord::class, 'compliance_record_id');
    }

    public function workPermitRecord(): BelongsTo
    {
        return $this->belongsTo(HcmEmployeeWorkPermit::class, 'compliance_record_id');
    }
}
