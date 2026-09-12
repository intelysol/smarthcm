<?php

namespace App\Domains\SelfService\Jobs;

use App\Domains\SelfService\Models\HrServiceRequest;
use App\Domains\SelfService\Services\RequestEscalationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessRequestEscalationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public HrServiceRequest $request,
        public int $level,
        public string $reason
    ) {}

    public function handle(RequestEscalationService $escalationService): void
    {
        $escalationService->escalate($this->request, $this->level, $this->reason);
    }
}
