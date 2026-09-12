<?php

namespace App\Domains\OrganizationDesign\Events;

use App\Domains\OrganizationDesign\Models\OrgDesignScenario;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrgDesignScenarioApproved
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public OrgDesignScenario $scenario
    ) {}
}
