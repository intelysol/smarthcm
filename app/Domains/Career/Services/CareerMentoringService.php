<?php

namespace App\Domains\Career\Services;

use App\Domains\Career\Events\CareerMentoringCompleted;
use App\Domains\Career\Events\CareerMentoringCreated;
use App\Domains\Career\Models\CareerMentoringGoal;
use App\Domains\Career\Models\CareerMentoringProgram;
use App\Domains\Career\Models\CareerMentoringRelationship;
use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Services\AuditService;
use Illuminate\Support\Facades\DB;

class CareerMentoringService
{
    public function __construct(protected AuditService $audit) {}

    public function createRelationship(
        Employee $mentor,
        Employee $mentee,
        ?CareerMentoringProgram $program = null,
        ?string $startDate = null,
        ?string $endDate = null
    ): CareerMentoringRelationship {
        return DB::transaction(function () use ($mentor, $mentee, $program, $startDate, $endDate) {
            $relationship = CareerMentoringRelationship::query()->create([
                'tenant_id' => $mentor->tenant_id,
                'program_id' => $program?->id,
                'mentor_employee_id' => $mentor->id,
                'mentee_employee_id' => $mentee->id,
                'start_date' => $startDate ?? now()->toDateString(),
                'end_date' => $endDate,
                'status' => 'active',
                'matching_score' => 85.0,
            ]);

            CareerMentoringCreated::dispatch($relationship);

            $this->audit->record(
                (string) $mentor->tenant_id,
                'CareerMentoringCreated',
                'create_mentoring',
                CareerMentoringRelationship::class,
                (string) $relationship->id,
                null,
                null,
                [
                    'mentor_id' => $mentor->id,
                    'mentee_id' => $mentee->id,
                ]
            );

            return $relationship;
        });
    }

    public function addGoal(CareerMentoringRelationship $relationship, string $title, ?string $description = null, ?string $targetDate = null): CareerMentoringGoal
    {
        return CareerMentoringGoal::query()->create([
            'tenant_id' => $relationship->tenant_id,
            'relationship_id' => $relationship->id,
            'title' => $title,
            'description' => $description,
            'target_date' => $targetDate,
            'status' => 'in_progress',
        ]);
    }

    public function completeRelationship(CareerMentoringRelationship $relationship): CareerMentoringRelationship
    {
        return DB::transaction(function () use ($relationship) {
            $relationship->update([
                'status' => 'completed',
                'end_date' => now()->toDateString(),
            ]);

            CareerMentoringCompleted::dispatch($relationship);
            return $relationship;
        });
    }
}
