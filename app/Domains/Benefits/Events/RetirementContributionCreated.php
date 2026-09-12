<?php

namespace App\Domains\Benefits\Events;

use App\Domains\Benefits\Models\RetirementTransaction;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RetirementContributionCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public RetirementTransaction $transaction
    ) {}
}
