<?php

namespace App\Domains\Attendance\Jobs;

use App\Domains\Attendance\Services\AttendanceProcessor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessAttendanceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $tenantId,
        public string $date
    ) {}

    public function handle(AttendanceProcessor $processor): void
    {
        $processor->processTenantDate($this->tenantId, $this->date);
    }
}
