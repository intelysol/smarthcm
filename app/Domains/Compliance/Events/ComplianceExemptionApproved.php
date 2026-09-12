<?php

namespace App\Domains\Compliance\Events;

use App\Domains\Compliance\Models\HcmComplianceExemption;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ComplianceExemptionApproved
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public HcmComplianceExemption $exemption
    ) {}
}
