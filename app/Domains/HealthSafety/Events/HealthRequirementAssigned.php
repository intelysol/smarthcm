<?php

declare(strict_types=1);

namespace App\Domains\HealthSafety\Events;

use App\Domains\HealthSafety\Models\HcmEmployeeHealthRequirement;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class HealthRequirementAssigned
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public HcmEmployeeHealthRequirement $requirement
    ) {}
}
