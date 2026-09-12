<?php

namespace App\Domains\Attendance\Jobs;

use App\Domains\Attendance\Services\TimesheetService;
use App\Domains\Employee\Models\Employee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateTimesheetsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $tenantId,
        public string $startDate,
        public string $endDate
    ) {}

    public function handle(TimesheetService $timesheetService): void
    {
        $employees = Employee::query()->where('tenant_id', $this->tenantId)->get();

        foreach ($employees as $emp) {
            $timesheetService->generateTimesheet($emp, $this->startDate, $this->endDate);
        }
    }
}
