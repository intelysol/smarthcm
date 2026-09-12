<?php

namespace App\Domains\Attendance\Jobs;

use App\Domains\Attendance\Models\AttendanceSession;
use App\Domains\Attendance\Services\LaborComplianceIntelligenceService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class EvaluateLaborComplianceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public string $sessionId)
    {
    }

    public function handle(LaborComplianceIntelligenceService $service): void
    {
        $session = AttendanceSession::find($this->sessionId);
        if ($session) {
            $service->evaluateSessionCompliance($session);
        }
    }
}