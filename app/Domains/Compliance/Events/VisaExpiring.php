<?php

namespace App\Domains\Compliance\Events;

use App\Domains\Compliance\Models\HcmEmployeeVisaRecord;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VisaExpiring
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public HcmEmployeeVisaRecord $visaRecord,
        public int $daysRemaining
    ) {}
}
