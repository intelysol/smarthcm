<?php

namespace App\Domains\EmployeeRelations\Jobs;

use App\Domains\EmployeeRelations\Models\EmployeeRelationCase;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateCaseReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public EmployeeRelationCase $case,
        public User $requestedBy,
        public string $reportType = 'case_bundle'
    ) {}

    public function handle(): void
    {
        Log::info("ER Case Export Bundle generated for Case #{$this->case->case_number} by User #{$this->requestedBy->id}");
    }
}
