<?php

namespace App\Domains\PersonalData\Jobs;

use App\Domains\PersonalData\Models\HcmEmployeeDataChangeRequest;
use App\Domains\PersonalData\Services\PersonalDataChangeRequestService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessEffectiveDatedChangesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public ?string $tenantId = null
    ) {}

    public function handle(PersonalDataChangeRequestService $service): void
    {
        $today = Carbon::today()->toDateString();

        $query = HcmEmployeeDataChangeRequest::with('items')
            ->where('status', 'approved')
            ->whereDate('effective_date', '<=', $today);

        if ($this->tenantId) {
            $query->where('tenant_id', $this->tenantId);
        }

        $pendingRequests = $query->get();

        foreach ($pendingRequests as $request) {
            $service->applyChanges($request);
            $request->update(['status' => 'applied']);
        }
    }
}
