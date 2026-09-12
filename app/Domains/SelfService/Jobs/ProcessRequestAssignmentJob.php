<?php

namespace App\Domains\SelfService\Jobs;

use App\Domains\SelfService\Models\HrServiceRequest;
use App\Domains\SelfService\Services\RequestAssignmentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessRequestAssignmentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public HrServiceRequest $request) {}

    public function handle(RequestAssignmentService $assignmentService): void
    {
        $assignmentService->routeAndAssign($this->request);
    }
}
