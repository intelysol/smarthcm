<?php

declare(strict_types=1);

namespace App\Domains\HealthSafety\Jobs;

use App\Domains\HealthSafety\Services\OccupationalHealthRequirementService;
use App\Domains\HR\Models\Employee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class EvaluateEmployeeHealthRequirementsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $employeeId
    ) {}

    public function handle(OccupationalHealthRequirementService $service): void
    {
        $employee = Employee::find($this->employeeId);
        if ($employee) {
            $service->assignApplicableRequirements($employee);
        }
    }
}
