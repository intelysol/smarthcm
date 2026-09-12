<?php

namespace App\Domains\Benefits\Events;

use App\Domains\Benefits\Models\InsuranceClaim;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InsuranceClaimSubmitted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public InsuranceClaim $claim
    ) {}
}
