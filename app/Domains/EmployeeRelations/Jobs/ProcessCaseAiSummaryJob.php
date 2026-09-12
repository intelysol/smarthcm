<?php

namespace App\Domains\EmployeeRelations\Jobs;

use App\Domains\EmployeeRelations\Models\EmployeeRelationCase;
use App\Domains\EmployeeRelations\Services\CaseAuthorizationService;
use App\Domains\EmployeeRelations\Services\EmployeeRelationAiService;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessCaseAiSummaryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public EmployeeRelationCase $case,
        public User $user
    ) {}

    public function handle(EmployeeRelationAiService $aiService, CaseAuthorizationService $auth): void
    {
        $aiService->generateCaseSummary($this->case, $this->user, $auth);
    }
}
