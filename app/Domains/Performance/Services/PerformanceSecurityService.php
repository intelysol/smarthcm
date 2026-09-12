<?php

declare(strict_types=1);

namespace App\Domains\Performance\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\Performance\Models\PerformanceFeedbackRequest;
use App\Domains\Performance\Models\PerformanceFeedbackResponse;
use App\Models\User;
use Illuminate\Support\Collection;

class PerformanceSecurityService
{
    /**
     * Check if user is authorized to manage or view performance within HR/admin scope.
     */
    public function isHrOrAdmin(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        if ($user->is_platform_admin ?? false) {
            return true;
        }

        $hasRole = $user->roles()->whereIn('name', ['hr_admin', 'hr_manager', 'performance_admin'])->exists();

        return $hasRole
            || $user->hasPermission('hcm.performance.view')
            || $user->hasPermission('hcm.performance.review.view')
            || $user->permissions()->whereIn('name', ['hcm.performance.view', 'hcm.performance.review.view'])->exists();
    }

    /**
     * Check if user is authorized to manage the employee (is employee direct/indirect manager or HR).
     */
    public function canManageEmployee(User $user, Employee $employee): bool
    {
        if ($this->isHrOrAdmin($user)) {
            return true;
        }

        // Verify if user is employee designated manager
        $userEmployee = Employee::where('user_id', $user->id)->first();
        if (!$userEmployee) {
            return false;
        }

        // Check reports_to / manager relationship or direct cycle assignment
        return $employee->reports_to_id === $userEmployee->id
            || $employee->manager_id === $userEmployee->id;
    }

    /**
     * Apply 360 anonymity rules.
     * If responses for an anonymous feedback group are fewer than threshold (e.g., minimum 3),
     * individual identities and comments MUST be masked or aggregated to protect reviewer identity.
     */
    public function filterFeedbackResponsesForViewer(
        Collection $responses,
        User $viewer,
        int $anonymityThreshold = 3
    ): Collection {
        $isHr = $this->isHrOrAdmin($viewer);

        return $responses->map(function (PerformanceFeedbackResponse $item) use ($responses, $viewer, $anonymityThreshold) {
            $request = $item->request;
            if (!$request) {
                return $item;
            }

            // If feedback was designated as anonymous
            if ($request->feedback_identity_hidden) {
                // If total responses in this cycle for this relationship type is under threshold, enforce strict anonymity
                $typeCount = $responses->filter(fn ($r) => $r->request?->relationship_type === $request->relationship_type)->count();

                if ($typeCount < $anonymityThreshold) {
                    $item->response = '[FEEDBACK ANONYMIZED - BELOW THRESHOLD OF ' . $anonymityThreshold . ' RESPONDENTS]';
                }
            }

            return $item;
        });
    }
}
