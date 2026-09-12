<?php

namespace App\Domains\Benefits\Events;

use App\Domains\Benefits\Models\LoanDisbursement;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LoanDisbursed
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public LoanDisbursement $disbursement
    ) {}
}
