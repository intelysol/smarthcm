<?php

namespace App\Domains\Career\Services;

use App\Domains\Career\Enums\PlanStatus;
use App\Domains\Career\Events\SuccessionCandidateAdded;
use App\Domains\Career\Events\SuccessionCandidateUpdated;
use App\Domains\Career\Events\SuccessionPlanCreated;
use App\Domains\Career\Events\SuccessionPositionRiskChanged;
use App\Domains\Career\Models\SuccessionCandidate;
use App\Domains\Career\Models\SuccessionDevelopmentAction;
use App\Domains\Career\Models\SuccessionPlan;
use App\Domains\Career\Models\SuccessionPosition;
use App\Domains\Career\Models\SuccessionScenario;
use App\Domains\Career\Models\SuccessionScenarioCandidate;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Job;
use App\Domains\Organization\Models\Position;
use App\Domains\Shared\Services\AuditService;
use Illuminate\Support\Facades\DB;

class SuccessionPlanService
{
    public function __construct(
        protected SuccessionRiskEngine $riskEngine,
        protected AuditService $audit
    ) {}

    public function createPlan(
        string $tenantId,
        string $name,
        ?string $description = null,
        string $scopeType = 'organization',
        ?string $scopeId = null,
        ?Employee $owner = null
    ): SuccessionPlan {
        return DB::transaction(function () use ($tenantId, $name, $description, $scopeType, $scopeId, $owner) {
            $plan = SuccessionPlan::query()->create([
                'tenant_id' => $tenantId,
                'name' => $name,
                'description' => $description,
                'scope_type' => $scopeType,
                'scope_id' => $scopeId,
                'owner_id' => $owner?->id,
                'status' => PlanStatus::Active->value,
                'review_date' => now()->addYear()->toDateString(),
            ]);

            SuccessionPlanCreated::dispatch($plan);

            $this->audit->record(
                $tenantId,
                'SuccessionPlanCreated',
                'create_succession_plan',
                SuccessionPlan::class,
                (string) $plan->id,
                null,
                null,
                ['name' => $name]
            );

            return $plan;
        });
    }

    public function addPositionToPlan(
        SuccessionPlan $plan,
        Position $position,
        Job $job,
        ?Employee $currentIncumbent = null,
        string $criticality = 'critical',
        string $vacancyRisk = 'medium'
    ): SuccessionPosition {
        return DB::transaction(function () use ($plan, $position, $job, $currentIncumbent, $criticality, $vacancyRisk) {
            $succPosition = SuccessionPosition::query()->create([
                'tenant_id' => $plan->tenant_id,
                'succession_plan_id' => $plan->id,
                'position_id' => $position->id,
                'job_id' => $job->id,
                'department_id' => $position->department_id,
                'current_incumbent_id' => $currentIncumbent?->id,
                'criticality' => $criticality,
                'vacancy_risk' => $vacancyRisk,
                'risk_score' => 50.0,
                'status' => 'active',
            ]);

            $this->riskEngine->recalculatePositionRisk($succPosition);

            return $succPosition;
        });
    }

    public function addCandidate(
        SuccessionPosition $position,
        Employee $employee,
        int $priority = 1,
        string $readinessTimeframe = 'ready_1_to_2_years',
        float $readinessScore = 70.0,
        ?float $potentialRating = null,
        ?float $performanceRating = null,
        bool $isEmergencyChoice = false
    ): SuccessionCandidate {
        return DB::transaction(function () use ($position, $employee, $priority, $readinessTimeframe, $readinessScore, $potentialRating, $performanceRating, $isEmergencyChoice) {
            $candidate = SuccessionCandidate::query()->updateOrCreate(
                [
                    'tenant_id' => $position->tenant_id,
                    'succession_position_id' => $position->id,
                    'employee_id' => $employee->id,
                ],
                [
                    'priority' => $priority,
                    'readiness_timeframe' => $readinessTimeframe,
                    'readiness_score' => $readinessScore,
                    'potential_rating' => $potentialRating,
                    'performance_rating' => $performanceRating,
                    'is_emergency_choice' => $isEmergencyChoice,
                    'development_status' => 'in_progress',
                ]
            );

            if ($isEmergencyChoice) {
                $position->update(['emergency_successor_id' => $employee->id]);
            }

            SuccessionCandidateAdded::dispatch($candidate);
            $this->riskEngine->recalculatePositionRisk($position);

            return $candidate;
        });
    }

    public function addDevelopmentAction(
        SuccessionCandidate $candidate,
        string $actionType,
        string $title,
        ?string $description = null,
        ?string $targetDate = null
    ): SuccessionDevelopmentAction {
        return SuccessionDevelopmentAction::query()->create([
            'tenant_id' => $candidate->tenant_id,
            'candidate_id' => $candidate->id,
            'action_type' => $actionType,
            'title' => $title,
            'description' => $description,
            'target_completion_date' => $targetDate,
            'status' => 'planned',
            'completion_percentage' => 0.0,
        ]);
    }

    public function createScenario(
        SuccessionPlan $plan,
        string $name,
        string $triggerEvent,
        ?string $description = null
    ): SuccessionScenario {
        return SuccessionScenario::query()->create([
            'tenant_id' => $plan->tenant_id,
            'succession_plan_id' => $plan->id,
            'name' => $name,
            'trigger_event' => $triggerEvent,
            'description' => $description,
            'status' => 'simulated',
        ]);
    }

    public function addScenarioCandidate(
        SuccessionScenario $scenario,
        SuccessionPosition $position,
        Employee $proposedSuccessor,
        string $roleAssignment = 'permanent',
        ?string $impactAnalysis = null
    ): SuccessionScenarioCandidate {
        return SuccessionScenarioCandidate::query()->create([
            'tenant_id' => $scenario->tenant_id,
            'scenario_id' => $scenario->id,
            'succession_position_id' => $position->id,
            'proposed_successor_id' => $proposedSuccessor->id,
            'role_assignment' => $roleAssignment,
            'impact_analysis' => $impactAnalysis,
        ]);
    }
}
