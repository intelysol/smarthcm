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

class SendCaseNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public EmployeeRelationCase $case,
        public string $notificationType,
        public ?int $recipientUserId = null
    ) {}

    public function handle(): void
    {
        // Safe dispatch: Never include raw confidential case payload in general logs
        Log::info("ER Case Notification dispatched: {$this->notificationType} for Case #{$this->case->case_number}");
    }
}
