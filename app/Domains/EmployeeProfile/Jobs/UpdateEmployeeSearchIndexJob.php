<?php

namespace App\Domains\EmployeeProfile\Jobs;

use App\Domains\Employee\Models\Employee;
use App\Domains\EmployeeProfile\Services\EmployeeOrgProjectionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateEmployeeSearchIndexJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public string $employeeId)
    {
    }

    public function handle(EmployeeOrgProjectionService $projectionService): void
    {
        $employee = Employee::find($this->employeeId);
        if ($employee) {
            $projectionService->projectEmployee($employee);
        }
    }
}
