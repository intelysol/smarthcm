<?php

declare(strict_types=1);

namespace App\Domains\HealthSafety\Events;

use App\Domains\HealthSafety\Models\HcmSafetyIncident;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SafetyIncidentReported
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public HcmSafetyIncident $incident
    ) {}
}
