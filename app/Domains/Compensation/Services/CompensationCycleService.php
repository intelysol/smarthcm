<?php

declare(strict_types=1);

namespace App\Domains\Compensation\Services;

use App\Domains\Compensation\Models\CompensationCycle;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class CompensationCycleService
{
    /**
     * Allowed lifecycle transitions
     */
    public const TRANSITIONS = [
        'draft' => ['configured', 'cancelled'],
        'configured' => ['open', 'draft'],
        'open' => ['manager_planning', 'draft'],
        'manager_planning' => ['hr_review', 'open'],
        'hr_review' => ['calibration', 'approval', 'manager_planning'],
        'calibration' => ['approval', 'hr_review'],
        'approval' => ['approved', 'hr_review'],
        'approved' => ['published', 'exported'],
        'published' => ['exported', 'closed'],
        'exported' => ['closed'],
        'closed' => [],
        'cancelled' => [],
    ];

    public function create(User $user, array $data): CompensationCycle
    {
        $tenantId = $user->tenant_id;

        return CompensationCycle::create([
            'tenant_id' => $tenantId,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'cycle_type' => $data['cycle_type'] ?? 'annual_merit',
            'currency' => $data['currency'] ?? 'USD',
            'starts_on' => $data['starts_on'],
            'ends_on' => $data['ends_on'],
            'effective_on' => $data['effective_on'],
            'status' => 'draft',
            'guidelines' => $data['guidelines'] ?? [],
            'eligibility_rules' => $data['eligibility_rules'] ?? [],
            'is_confidential' => (bool) ($data['is_confidential'] ?? false),
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);
    }

    public function configure(User $user, CompensationCycle $cycle, array $guidelines, ?array $eligibility = null): CompensationCycle
    {
        $cycle->update([
            'guidelines' => $guidelines,
            'eligibility_rules' => $eligibility ?? $cycle->eligibility_rules,
            'status' => $cycle->status === 'draft' ? 'configured' : $cycle->status,
            'updated_by' => $user->id,
        ]);

        return $cycle->fresh();
    }

    public function transition(User $user, CompensationCycle $cycle, string $targetStatus): CompensationCycle
    {
        $currentStatus = $cycle->status;
        $allowed = self::TRANSITIONS[$currentStatus] ?? [];

        if (! in_array($targetStatus, $allowed, true)) {
            throw ValidationException::withMessages([
                'status' => "Cannot transition compensation cycle from '{$currentStatus}' to '{$targetStatus}'.",
            ]);
        }

        $cycle->update([
            'status' => $targetStatus,
            'updated_by' => $user->id,
        ]);

        return $cycle->fresh();
    }
}
