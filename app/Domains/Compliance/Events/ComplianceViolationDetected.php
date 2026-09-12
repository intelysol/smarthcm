<?php

namespace App\Domains\Compliance\Events;

use App\Domains\Compliance\Models\HcmEmployeeComplianceRequirement;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ComplianceViolationDetected
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public HcmEmployeeComplianceRequirement $assignment,
        public string $reason
    ) {}
}
