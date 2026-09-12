<?php

namespace App\Domains\Expenses\Events;

use App\Domains\Expenses\Models\TravelRequest;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TravelRequestApproved
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public TravelRequest $travelRequest) {}
}
