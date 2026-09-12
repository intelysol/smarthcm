<?php

namespace App\Domains\Compliance\Events;

use App\Domains\Compliance\Models\HcmEmployeeWorkPermit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WorkPermitExpiring
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public HcmEmployeeWorkPermit $workPermit,
        public int $daysRemaining
    ) {}
}
