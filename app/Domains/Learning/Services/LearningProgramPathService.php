<?php

namespace App\Domains\Learning\Services;

use App\Domains\Learning\Models\LearningCourse;
use App\Domains\Learning\Models\LearningPath;
use App\Domains\Learning\Models\LearningPathItem;
use App\Domains\Learning\Models\LearningProgram;
use App\Domains\Learning\Models\LearningProgramCourse;
use App\Domains\Shared\Services\AuditService;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class LearningProgramPathService
{
    public function __construct(
        private readonly AuditService $audit
    ) {}

    public function createProgram(User $actor, array $attributes): LearningProgram
    {
        return DB::transaction(function () use ($actor, $attributes) {
            $tenantId = (string) ($attributes['tenant_id'] ?? $actor->tenant_id);

            $program = LearningProgram::query()->create([
                ...$attributes,
                'tenant_id' => $tenantId,
                'status' => $attributes['status'] ?? 'draft',
            ]);

            $this->audit->record(
                $tenantId,
                'LearningProgramCreated',
                'create_program',
                LearningProgram::class,
                (string) $program->id,
                $actor->id,
                null,
                ['code' => $program->code, 'title' => $program->title]
            );

            return $program;
        });
    }

    public function addCourseToProgram(LearningProgram $program, LearningCourse $course, bool $isRequired = true, int $sortOrder = 0): LearningProgramCourse
    {
        return LearningProgramCourse::query()->updateOrCreate(
            [
                'program_id' => $program->id,
                'course_id' => $course->id,
            ],
            [
                'is_required' => $isRequired,
                'sort_order' => $sortOrder,
            ]
        );
    }

    public function createPath(User $actor, array $attributes): LearningPath
    {
        return DB::transaction(function () use ($actor, $attributes) {
            $tenantId = (string) ($attributes['tenant_id'] ?? $actor->tenant_id);

            $path = LearningPath::query()->create([
                ...$attributes,
                'tenant_id' => $tenantId,
                'status' => $attributes['status'] ?? 'draft',
            ]);

            $this->audit->record(
                $tenantId,
                'LearningPathCreated',
                'create_path',
                LearningPath::class,
                (string) $path->id,
                $actor->id,
                null,
                ['code' => $path->code, 'title' => $path->title]
            );

            return $path;
        });
    }

    public function addItemToPath(
        LearningPath $path,
        string $itemType,
        string $itemId,
        string $requirementLevel = 'required',
        int $sortOrder = 0
    ): LearningPathItem {
        return LearningPathItem::query()->create([
            'learning_path_id' => $path->id,
            'item_type' => $itemType,
            'item_id' => $itemId,
            'requirement_level' => $requirementLevel,
            'sort_order' => $sortOrder,
        ]);
    }
}
