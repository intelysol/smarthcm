<?php

namespace App\Domains\OrganizationDesign\Events;

use App\Domains\OrganizationDesign\Models\OrgDesignHealthIssue;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ArchitectureHealthAnomalyDetected
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public OrgDesignHealthIssue $issue
    ) {}
}
