<?php

namespace App\Domains\EmployeeProfile\Jobs;

use App\Domains\EmployeeProfile\Models\EmployeeProfileChangeRequest;
use App\Domains\EmployeeProfile\Services\ProfileChangeRequestService;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessProfileChangeRequestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $requestId,
        public string $reviewerId,
        public string $decision, // approve or reject
        public ?string $reasonOrComments = null
    ) {
    }

    public function handle(ProfileChangeRequestService $service): void
    {
        $request = EmployeeProfileChangeRequest::find($this->requestId);
        $reviewer = User::find($this->reviewerId);

        if (!$request || !$reviewer) {
            return;
        }

        if ($this->decision === 'approve') {
            $service->approveRequest($request, $reviewer, $this->reasonOrComments);
        } elseif ($this->decision === 'reject') {
            $service->rejectRequest($request, $reviewer, $this->reasonOrComments ?? 'Change rejected by reviewer');
        }
    }
}
