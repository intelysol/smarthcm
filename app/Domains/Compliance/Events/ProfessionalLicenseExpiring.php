<?php

namespace App\Domains\Compliance\Events;

use App\Domains\Compliance\Models\HcmEmployeeLicense;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProfessionalLicenseExpiring
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public HcmEmployeeLicense $license,
        public int $daysRemaining
    ) {}
}
