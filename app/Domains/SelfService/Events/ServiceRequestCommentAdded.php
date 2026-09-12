<?php

namespace App\Domains\SelfService\Events;

use App\Domains\SelfService\Models\HrServiceRequestComment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ServiceRequestCommentAdded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public HrServiceRequestComment $comment) {}
}
