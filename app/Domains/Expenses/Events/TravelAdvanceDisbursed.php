<?php

namespace App\Domains\Expenses\Events;

use App\Domains\Expenses\Models\TravelAdvance;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TravelAdvanceDisbursed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public TravelAdvance $advance) {}
}
