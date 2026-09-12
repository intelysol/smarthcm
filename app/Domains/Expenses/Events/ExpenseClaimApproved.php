<?php

namespace App\Domains\Expenses\Events;

use App\Domains\Expenses\Models\ExpenseClaim;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ExpenseClaimApproved
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public ExpenseClaim $claim) {}
}
