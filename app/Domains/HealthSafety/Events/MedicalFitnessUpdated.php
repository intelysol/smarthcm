<?php

declare(strict_types=1);

namespace App\Domains\HealthSafety\Events;

use App\Domains\HealthSafety\Models\HcmMedicalFitnessRecord;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MedicalFitnessUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public HcmMedicalFitnessRecord $fitnessRecord
    ) {}
}
