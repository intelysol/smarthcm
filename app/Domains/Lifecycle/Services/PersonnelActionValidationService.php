<?php

namespace App\Domains\Lifecycle\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\Lifecycle\Models\PersonnelActionRequest;
use App\Domains\Lifecycle\Models\PersonnelActionType;
use App\Domains\Organization\Models\Position;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class PersonnelActionValidationService
{
    public function __construct(protected ?PersonnelActionConflictService $conflictService = null)
    {
        $this->conflictService = $conflictService ?? new PersonnelActionConflictService();
    }

    public function validateRequestCreation(User $user, Employee $employee, PersonnelActionType $actionType, Carbon $effectiveDate, array $changes): void
    {
        // 1. Backdated validation: only authorized users can submit backdated changes
        if ($effectiveDate->lessThan(now()->startOfDay())) {
            $canBackdate = $user->is_platform_admin || (method_exists($user, 'hasPermission') && $user->hasPermission('personnel_actions.backdate'));
            if (!$canBackdate) {
                throw ValidationException::withMessages([
                    'effective_date' => 'Backdated personnel actions require special authorization ("personnel_actions.backdate").',
                ]);
            }
        }

        // 2. Position availability validation
        foreach ($changes as $change) {
            if ($change['field_name'] === 'position_id' && !empty($change['new_value'])) {
                $pos = Position::find($change['new_value']);
                if (!$pos) {
                    throw ValidationException::withMessages(['position' => 'Target position does not exist.']);
                }
                if ($pos->is_frozen ?? false) {
                    throw ValidationException::withMessages(['position' => 'Cannot assign employee to a frozen position.']);
                }
            }
        }
    }

    public function validateSubmission(PersonnelActionRequest $request): void
    {
        // 1. Conflict detection
        $conflicts = $this->conflictService->detectConflicts($request);
        if (!empty($conflicts)) {
            throw ValidationException::withMessages([
                'conflict' => 'Blocking Conflict: ' . implode(', ', $conflicts),
            ]);
        }
    }
}
