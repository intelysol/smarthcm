<?php

namespace App\Domains\Learning\Jobs;

use App\Domains\Learning\Models\LearningCertificate;
use App\Domains\Learning\Services\LearningCertificateService;
use App\Domains\Platform\Contracts\TenantContext;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessCertificateExpiryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public readonly string $tenantId) {}

    public function handle(LearningCertificateService $certificateService, TenantContext $tenantContext): void
    {
        $tenant = Tenant::query()->find($this->tenantId);
        if (! $tenant) return;
        $tenantContext->set($tenant);

        $expired = LearningCertificate::query()
            ->where('tenant_id', $this->tenantId)
            ->where('status', 'active')
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<', now()->toDateString())
            ->get();

        foreach ($expired as $cert) {
            $certificateService->expireCertificate($cert);
        }
    }
}
