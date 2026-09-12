<?php

namespace App\Domains\Benefits\Jobs;

use App\Domains\Benefits\Models\BenefitEnrollment;
use App\Domains\Benefits\Services\BenefitEnrollmentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessBenefitEnrollmentsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $tenantId
    ) {}

    public function handle(BenefitEnrollmentService $service): void
    {
        $today = now()->toDateString();

        $enrollmentsToActivate = BenefitEnrollment::query()
            ->where('tenant_id', $this->tenantId)
            ->where('status', 'approved')
            ->whereDate('effective_from', '<=', $today)
            ->get();

        foreach ($enrollmentsToActivate as $enrollment) {
            $service->activateEnrollment($enrollment);
        }
    }
}
