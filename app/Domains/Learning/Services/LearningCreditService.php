<?php

namespace App\Domains\Learning\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\Learning\Models\LearningCredit;
use App\Domains\Learning\Models\LearningCreditTransaction;
use App\Domains\Shared\Services\AuditService;
use Illuminate\Support\Facades\DB;

class LearningCreditService
{
    public function __construct(
        private readonly AuditService $audit
    ) {}

    public function awardCredits(
        Employee $employee,
        float $credits,
        string $sourceType,
        ?string $sourceId,
        string $description,
        ?int $awardedBy = null
    ): LearningCreditTransaction {
        return DB::transaction(function () use ($employee, $credits, $sourceType, $sourceId, $description, $awardedBy) {
            $transaction = LearningCreditTransaction::query()->create([
                'tenant_id' => $employee->tenant_id,
                'employee_id' => $employee->id,
                'source_type' => $sourceType,
                'source_id' => $sourceId,
                'credits' => $credits,
                'description' => $description,
                'awarded_at' => now(),
                'awarded_by' => $awardedBy,
            ]);

            $creditRecord = LearningCredit::query()->firstOrCreate(
                [
                    'tenant_id' => $employee->tenant_id,
                    'employee_id' => $employee->id,
                ],
                [
                    'total_earned' => 0,
                    'total_required' => 0,
                    'period_start' => now()->startOfYear()->toDateString(),
                    'period_end' => now()->endOfYear()->toDateString(),
                ]
            );

            $creditRecord->increment('total_earned', $credits);

            $this->audit->record(
                (string) $employee->tenant_id,
                'LearningCreditsAwarded',
                'award_credits',
                LearningCreditTransaction::class,
                (string) $transaction->id,
                $awardedBy,
                null,
                ['credits' => $credits, 'employee_id' => $employee->id, 'source_type' => $sourceType]
            );

            return $transaction;
        });
    }

    public function getBalance(Employee $employee): float
    {
        return (float) (LearningCredit::query()
            ->where('tenant_id', $employee->tenant_id)
            ->where('employee_id', $employee->id)
            ->value('total_earned') ?? 0.0);
    }
}
