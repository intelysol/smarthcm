<?php

namespace App\Domains\Career\Services;

use App\Domains\Career\Enums\PlanStatus;
use App\Domains\Career\Events\CareerDevelopmentActionCompleted;
use App\Domains\Career\Events\CareerDevelopmentActionCreated;
use App\Domains\Career\Events\CareerPlanApproved;
use App\Domains\Career\Events\CareerPlanCompleted;
use App\Domains\Career\Events\CareerPlanCreated;
use App\Domains\Career\Events\CareerPlanUpdated;
use App\Domains\Career\Models\CareerPlan;
use App\Domains\Career\Models\CareerPlanAction;
use App\Domains\Employee\Models\Employee;
use App\Domains\Events\Services\EventBus;
use App\Domains\Organization\Models\Job;
use App\Domains\Shared\Services\AuditService;
use Illuminate\Support\Facades\DB;

class CareerPlanService
{
    public function __construct(
        protected CareerReadinessEngine $readinessEngine,
        protected CareerSkillGapService $gapService,
        protected AuditService $audit,
        protected EventBus $events
    ) {}

    public function createPlan(
        Employee $employee,
        ?Job $targetJob = null,
        ?string $targetDate = null,
        string $visibility = 'private'
    ): CareerPlan {
        return DB::transaction(function () use ($employee, $targetJob, $targetDate, $visibility) {
            $readiness = $targetJob
                ? $this->readinessEngine->calculateReadiness($employee, $targetJob)
                : ['score' => 0.0, 'level' => 'developing'];

            $plan = CareerPlan::query()->create([
                'tenant_id' => $employee->tenant_id,
                'employee_id' => $employee->id,
                'current_job_id' => $employee->current_position_id, // or designation
                'target_job_id' => $targetJob?->id,
                'target_date' => $targetDate,
                'readiness_level' => $readiness['level'],
                'readiness_score' => $readiness['score'],
                'visibility' => $visibility,
                'status' => PlanStatus::Draft->value,
            ]);

            if ($targetJob) {
                $this->gapService->analyzeGapsForTargetJob($employee, $targetJob);
            }

            CareerPlanCreated::dispatch($plan);

            $this->audit->record(
                (string) $employee->tenant_id,
                'CareerPlanCreated',
                'create_career_plan',
                CareerPlan::class,
                (string) $plan->id,
                null,
                null,
                [
                    'employee_id' => $employee->id,
                    'target_job_id' => $targetJob?->id,
                    'readiness_score' => $readiness['score'],
                ]
            );

            return $plan;
        });
    }

    public function approvePlan(CareerPlan $plan, Employee $approver): CareerPlan
    {
        return DB::transaction(function () use ($plan, $approver) {
            $plan->update([
                'status' => PlanStatus::Approved->value,
                'approved_by' => $approver->id,
                'approved_at' => now(),
            ]);

            CareerPlanApproved::dispatch($plan);

            $this->audit->record(
                (string) $plan->tenant_id,
                'CareerPlanApproved',
                'approve_career_plan',
                CareerPlan::class,
                (string) $plan->id,
                null,
                null,
                [
                    'employee_id' => $plan->employee_id,
                    'approved_by' => $approver->id,
                ]
            );

            return $plan;
        });
    }

    public function addAction(
        CareerPlan $plan,
        string $actionType,
        string $title,
        ?string $description = null,
        ?string $startDate = null,
        ?string $dueDate = null
    ): CareerPlanAction {
        return DB::transaction(function () use ($plan, $actionType, $title, $description, $startDate, $dueDate) {
            $action = CareerPlanAction::query()->create([
                'tenant_id' => $plan->tenant_id,
                'plan_id' => $plan->id,
                'action_type' => $actionType,
                'title' => $title,
                'description' => $description,
                'start_date' => $startDate,
                'due_date' => $dueDate,
                'status' => 'planned',
                'completion_percentage' => 0.0,
            ]);

            CareerDevelopmentActionCreated::dispatch($action);
            return $action;
        });
    }

    public function updateActionProgress(CareerPlanAction $action, float $completionPercentage, string $status = 'in_progress'): CareerPlanAction
    {
        return DB::transaction(function () use ($action, $completionPercentage, $status) {
            $completedAt = ($completionPercentage >= 100.0 || $status === 'completed') ? now() : null;
            if ($completedAt) {
                $status = 'completed';
                $completionPercentage = 100.0;
            }

            $action->update([
                'completion_percentage' => $completionPercentage,
                'status' => $status,
                'completed_at' => $completedAt,
            ]);

            if ($status === 'completed') {
                CareerDevelopmentActionCompleted::dispatch($action);

                // Recalculate plan readiness
                $this->readinessEngine->updatePlanReadiness($action->plan);
            }

            return $action;
        });
    }
}
