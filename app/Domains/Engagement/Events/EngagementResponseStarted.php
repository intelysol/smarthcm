<?php

namespace App\Domains\Engagement\Events;

use App\Domains\Engagement\Models\EngagementResponse;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EngagementResponseStarted
{
    use Dispatchable, SerializesModels;
    public function __construct(public EngagementResponse $response) {}
}
