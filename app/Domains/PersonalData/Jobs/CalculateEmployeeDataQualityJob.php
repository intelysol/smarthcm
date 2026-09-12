<?php

namespace App\Domains\PersonalData\Jobs;

use App\Domains\Employee\Models\Employee;
use App\Domains\PersonalData\Services\EmployeeDataQualityService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CalculateEmployeeDataQualityJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public ?string $employeeId = null,
        public ?string $tenantId = null
    ) {}

    public function handle(EmployeeDataQualityService $service): void
    {
        if ($this->employeeId) {
            $service->calculateQuality($this->employeeId);
        } elseif ($this->tenantId) {
            $employees = Employee::where('tenant_id', $this->tenantId)->pluck('id');
            foreach ($employees as $empId) {
                $service->calculateQuality($empId);
            }
        }
    }
}
