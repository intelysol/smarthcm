<?php

namespace App\Domains\SelfService\Events;

use App\Domains\SelfService\Models\HrServiceRequest;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ServiceRequestReopened
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public HrServiceRequest $request) {}
}
