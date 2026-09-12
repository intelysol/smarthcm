<?php

namespace App\Domains\Benefits\Events;

use App\Domains\Benefits\Models\BenefitEnrollment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BenefitEnrollmentApproved
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public BenefitEnrollment $enrollment
    ) {}
}
