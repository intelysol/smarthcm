<?php

namespace App\Domains\Absence\Jobs;

use App\Domains\Absence\Models\HcmAbsenceEvent;
use App\Domains\Absence\Services\AbsenceOperationalImpactService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessAbsenceEventJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public string $absenceEventId)
    {
    }

    public function handle(AbsenceOperationalImpactService $service): void
    {
        $event = HcmAbsenceEvent::find($this->absenceEventId);
        if ($event) {
            $service->evaluateImpact($event);
        }
    }
}