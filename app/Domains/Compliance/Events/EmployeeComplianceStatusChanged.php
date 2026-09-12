<?php

namespace App\Domains\Compliance\Events;

use App\Domains\Compliance\Models\HcmEmployeeComplianceSnapshot;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EmployeeComplianceStatusChanged
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public HcmEmployeeComplianceSnapshot $snapshot,
        public string $previousStatus
    ) {}
}
