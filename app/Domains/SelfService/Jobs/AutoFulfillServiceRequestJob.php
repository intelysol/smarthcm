<?php

namespace App\Domains\SelfService\Jobs;

use App\Domains\SelfService\Events\ServiceRequestAutoFulfilled;
use App\Domains\SelfService\Models\HrServiceRequest;
use App\Domains\SelfService\Services\ServiceAutomationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AutoFulfillServiceRequestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public HrServiceRequest $request
    ) {}

    public function handle(ServiceAutomationService $service): void
    {
        $document = $service->autoFulfill($this->request);

        if ($document) {
            event(new ServiceRequestAutoFulfilled($this->request, $document));
        }
    }
}
