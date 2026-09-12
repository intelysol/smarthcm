<?php

namespace App\Domains\Benefits\Events;

use App\Domains\Benefits\Models\LoanApplication;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LoanApproved
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public LoanApplication $application
    ) {}
}
