<?php

namespace App\Domains\Benefits\Services;

use App\Domains\Benefits\Enums\EnrollmentStatus;
use App\Domains\Benefits\Enums\EnrollmentType;
use App\Domains\Benefits\Models\BenefitBeneficiary;
use App\Domains\Benefits\Models\BenefitCoverage;
use App\Domains\Benefits\Models\BenefitDependent;
use App\Domains\Benefits\Models\BenefitElection;
use App\Domains\Benefits\Models\BenefitEnrollment;
use App\Domains\Benefits\Models\BenefitEnrollmentWindow;
use App\Domains\Benefits\Models\BenefitLifeEvent;
use App\Domains\Benefits\Models\BenefitPlan;
use App\Domains\Employee\Models\Employee;
use App\Domains\Employee\Models\EmployeeFamilyMember;
use App\Domains\Shared\Services\AuditService;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BenefitElectionService
{
    public function __construct(
        protected BenefitEligibilityService $eligibilityService,
        protected BenefitCoverageService $coverageService,
        protected AuditService $auditService
    ) {}

    /**
     * Submit or update an employee benefit election.
     */
    public function elect(Employee $employee, BenefitPlan $plan, array $data, ?User $actor = null): BenefitElection
    {
        // 1. Eligibility Check
        $eligibility = $this->eligibilityService->evaluateEligibility($employee, $plan);
        if ($eligibility->status === 'not_eligible') {
            throw ValidationException::withMessages([
                'benefit_plan' => "Employee {$employee->employee_number} is not eligible for plan '{$plan->name}': {$eligibility->reason}",
            ]);
        }

        // 2. Waiver Enforcement
        $isWaived = (bool) ($data['is_waived'] ?? false);
        if ($isWaived) {
            if ($plan->is_mandatory) {
                throw ValidationException::withMessages([
                    'waiver' => "Plan '{$plan->name}' is mandatory and cannot be waived.",
                ]);
            }
            if (! $plan->is_waivable) {
                throw ValidationException::withMessages([
                    'waiver' => "Plan '{$plan->name}' does not permit waivers.",
                ]);
            }
        }

        // 3. Coverage Tier & Estimated Cost Calculation
        $coverageTier = null;
        if (! empty($data['benefit_coverage_id'])) {
            $coverageTier = BenefitCoverage::where('benefit_plan_id', $plan->id)
                ->where('id', $data['benefit_coverage_id'])
                ->first();
        }

        $costs = $isWaived
            ? ['employee_cost' => 0, 'employer_cost' => 0, 'total_cost' => 0]
            : $this->coverageService->calculateEstimatedCosts($plan, $coverageTier);

        // 4. Beneficiary Allocation Validation (Must sum to 100%)
        $beneficiaries = $data['beneficiaries'] ?? [];
        if (! empty($beneficiaries) && ! $isWaived) {
            $totalAllocation = 0.0;
            foreach ($beneficiaries as $b) {
                $totalAllocation += (float) ($b['percentage_allocation'] ?? 0);
            }
            if (abs($totalAllocation - 100.00) > 0.01) {
                throw ValidationException::withMessages([
                    'beneficiaries' => "Total beneficiary allocation must equal exactly 100%. Current sum: {$totalAllocation}%.",
                ]);
            }
        }

        // 5. Dependent Validation against Epic 2.32
        $dependents = $data['dependents'] ?? [];
        if (! empty($dependents) && ! $isWaived) {
            if ($coverageTier && $coverageTier->max_dependents > 0 && count($dependents) > $coverageTier->max_dependents) {
                throw ValidationException::withMessages([
                    'dependents' => "Selected coverage tier allows a maximum of {$coverageTier->max_dependents} dependents.",
                ]);
            }
        }

        return DB::transaction(function () use ($employee, $plan, $data, $coverageTier, $costs, $isWaived, $beneficiaries, $dependents, $actor) {
            $election = BenefitElection::updateOrCreate(
                [
                    'tenant_id' => $employee->tenant_id,
                    'employee_id' => $employee->id,
                    'benefit_plan_id' => $plan->id,
                    'benefit_enrollment_window_id' => $data['benefit_enrollment_window_id'] ?? null,
                ],
                [
                    'benefit_life_event_id' => $data['benefit_life_event_id'] ?? null,
                    'benefit_coverage_id' => $coverageTier?->id,
                    'coverage_level' => $data['coverage_level'] ?? ($coverageTier?->name ?? 'employee_only'),
                    'election_date' => $data['election_date'] ?? now()->toDateString(),
                    'effective_date' => $data['effective_date'] ?? ($data['election_date'] ?? now()->toDateString()),
                    'employee_cost_estimated' => $costs['employee_cost'],
                    'employer_cost_estimated' => $costs['employer_cost'],
                    'total_cost_estimated' => $costs['total_cost'],
                    'currency' => $plan->currency ?? 'USD',
                    'status' => $data['status'] ?? 'elected',
                    'is_waived' => $isWaived,
                    'waiver_reason' => $isWaived ? ($data['waiver_reason'] ?? null) : null,
                    'supporting_document_id' => $data['supporting_document_id'] ?? null,
                    'selected_dependents' => $dependents,
                    'beneficiaries_data' => $beneficiaries,
                    'notes' => $data['notes'] ?? null,
                ]
            );

            $this->auditService->record(
                tenantId: $employee->tenant_id,
                eventType: 'benefit_election.submitted',
                action: 'save',
                entityType: BenefitElection::class,
                entityId: $election->id,
                actorId: $actor?->id,
                after: $election->toArray()
            );

            return $election;
        });
    }

    /**
     * Confirm election by employee before final HR submission
     */
    public function confirmElection(BenefitElection $election, ?User $actor = null): BenefitElection
    {
        $election->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);

        $this->auditService->record(
            tenantId: $election->tenant_id,
            eventType: 'benefit_election.confirmed',
            action: 'confirm',
            entityType: BenefitElection::class,
            entityId: $election->id,
            actorId: $actor?->id,
            after: $election->toArray()
        );

        return $election;
    }

    /**
     * Approve election and generate/activate authoritative BenefitEnrollment
     */
    public function approveElection(BenefitElection $election, User $approver): BenefitEnrollment
    {
        return DB::transaction(function () use ($election, $approver) {
            $election->update([
                'status' => 'approved',
                'approved_by' => $approver->id,
                'approved_at' => now(),
            ]);

            // If waived, do not create active insurance/benefit enrollment
            if ($election->is_waived) {
                $enrollment = BenefitEnrollment::updateOrCreate(
                    [
                        'tenant_id' => $election->tenant_id,
                        'employee_id' => $election->employee_id,
                        'benefit_plan_id' => $election->benefit_plan_id,
                        'benefit_enrollment_window_id' => $election->benefit_enrollment_window_id,
                    ],
                    [
                        'enrollment_type' => EnrollmentType::OPEN_ENROLLMENT->value,
                        'coverage_level' => 'waived',
                        'effective_from' => $election->effective_date,
                        'employee_contribution' => 0,
                        'employer_contribution' => 0,
                        'currency' => $election->currency,
                        'status' => EnrollmentStatus::CANCELLED->value,
                        'notes' => "Benefit waived by employee: {$election->waiver_reason}",
                        'approved_by' => $approver->id,
                        'approved_at' => now(),
                    ]
                );

                return $enrollment;
            }

            // Create active enrollment record
            $enrollment = BenefitEnrollment::updateOrCreate(
                [
                    'tenant_id' => $election->tenant_id,
                    'employee_id' => $election->employee_id,
                    'benefit_plan_id' => $election->benefit_plan_id,
                    'benefit_enrollment_window_id' => $election->benefit_enrollment_window_id,
                ],
                [
                    'enrollment_type' => $election->benefit_life_event_id ? EnrollmentType::LIFE_EVENT->value : EnrollmentType::OPEN_ENROLLMENT->value,
                    'coverage_level' => $election->coverage_level,
                    'effective_from' => $election->effective_date,
                    'employee_contribution' => $election->employee_cost_estimated,
                    'employer_contribution' => $election->employer_cost_estimated,
                    'currency' => $election->currency,
                    'status' => EnrollmentStatus::APPROVED->value,
                    'notes' => $election->notes,
                    'approved_by' => $approver->id,
                    'approved_at' => now(),
                ]
            );

            // Sync dependents from Epic 2.32
            if (! empty($election->selected_dependents)) {
                BenefitDependent::where('benefit_enrollment_id', $enrollment->id)->delete();
                foreach ($election->selected_dependents as $dep) {
                    $familyMember = isset($dep['family_member_id'])
                        ? EmployeeFamilyMember::find($dep['family_member_id'])
                        : null;

                    BenefitDependent::create([
                        'tenant_id' => $election->tenant_id,
                        'benefit_enrollment_id' => $enrollment->id,
                        'employee_id' => $election->employee_id,
                        'family_member_id' => $familyMember?->id ?? ($dep['family_member_id'] ?? null),
                        'name' => $familyMember?->name ?? ($dep['name'] ?? 'Dependent'),
                        'relationship' => $familyMember?->relationship ?? ($dep['relationship'] ?? 'dependent'),
                        'date_of_birth' => $familyMember?->date_of_birth ?? ($dep['date_of_birth'] ?? null),
                        'gender' => $dep['gender'] ?? null,
                        'national_id' => $dep['national_id'] ?? null,
                        'is_eligible' => true,
                        'effective_from' => $election->effective_date,
                    ]);
                }
            }

            // Sync beneficiaries
            if (! empty($election->beneficiaries_data)) {
                BenefitBeneficiary::where('benefit_enrollment_id', $enrollment->id)->delete();
                foreach ($election->beneficiaries_data as $ben) {
                    BenefitBeneficiary::create([
                        'tenant_id' => $election->tenant_id,
                        'benefit_enrollment_id' => $enrollment->id,
                        'employee_id' => $election->employee_id,
                        'plan_type' => $election->plan->benefit_type,
                        'name' => $ben['name'],
                        'relationship' => $ben['relationship'],
                        'percentage_allocation' => (float) $ben['percentage_allocation'],
                        'contact_phone' => $ben['contact_phone'] ?? null,
                        'contact_email' => $ben['contact_email'] ?? null,
                        'effective_from' => $election->effective_date,
                        'is_primary' => (bool) ($ben['is_primary'] ?? true),
                        'is_contingent' => (bool) ($ben['is_contingent'] ?? false),
                    ]);
                }
            }

            $this->auditService->record(
                tenantId: $election->tenant_id,
                eventType: 'benefit_election.approved',
                action: 'approve',
                entityType: BenefitEnrollment::class,
                entityId: $enrollment->id,
                actorId: $approver->id,
                after: $enrollment->toArray()
            );

            return $enrollment;
        });
    }

    public function getElectionsForEmployee(Employee $employee, ?string $windowId = null): Collection
    {
        $query = BenefitElection::query()
            ->where('tenant_id', $employee->tenant_id)
            ->where('employee_id', $employee->id)
            ->with(['plan', 'coverage', 'window']);

        if ($windowId) {
            $query->where('benefit_enrollment_window_id', $windowId);
        }

        return $query->get();
    }
}
