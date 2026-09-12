<?php

namespace App\Domains\SelfService\Jobs;

use App\Domains\SelfService\Events\ServiceDuplicateDetected;
use App\Domains\SelfService\Models\HrServiceRequest;
use App\Domains\SelfService\Services\ServiceDuplicateDetectionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DetectDuplicateServiceRequestsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public HrServiceRequest $request
    ) {}

    public function handle(ServiceDuplicateDetectionService $service): void
    {
        $duplicates = $service->findPotentialDuplicates($this->request);

        if ($duplicates->isNotEmpty()) {
            event(new ServiceDuplicateDetected($this->request, $duplicates));
        }
    }
}
