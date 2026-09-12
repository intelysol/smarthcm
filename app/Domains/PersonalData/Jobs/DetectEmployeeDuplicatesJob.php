<?php

namespace App\Domains\PersonalData\Jobs;

use App\Domains\PersonalData\Services\DuplicateDetectionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DetectEmployeeDuplicatesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $tenantId,
        public ?string $employeeId = null
    ) {}

    public function handle(DuplicateDetectionService $service): void
    {
        // Executes duplicate heuristic analysis (strictly advisory)
        $service->detectDuplicates($this->tenantId, $this->employeeId);
    }
}
