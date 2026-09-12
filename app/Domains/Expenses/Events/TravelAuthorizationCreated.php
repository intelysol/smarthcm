<?php

namespace App\Domains\Expenses\Events;

use App\Domains\Expenses\Models\TravelAuthorization;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TravelAuthorizationCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public TravelAuthorization $authorization) {}
}
