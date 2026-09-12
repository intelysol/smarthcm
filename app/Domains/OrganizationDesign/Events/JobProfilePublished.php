<?php

namespace App\Domains\OrganizationDesign\Events;

use App\Domains\OrganizationDesign\Models\JobProfile;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class JobProfilePublished
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public JobProfile $profile
    ) {}
}
