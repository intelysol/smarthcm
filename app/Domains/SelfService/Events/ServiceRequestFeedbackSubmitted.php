<?php

namespace App\Domains\SelfService\Events;

use App\Domains\SelfService\Models\HrServiceFeedback;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ServiceRequestFeedbackSubmitted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public HrServiceFeedback $feedback
    ) {}
}
