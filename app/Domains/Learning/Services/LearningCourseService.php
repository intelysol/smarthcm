<?php

namespace App\Domains\Learning\Services;

use App\Domains\Events\Services\EventBus;
use App\Domains\Learning\Events\LearningCourseCreated;
use App\Domains\Learning\Events\LearningCoursePublished;
use App\Domains\Learning\Events\LearningCourseVersionCreated;
use App\Domains\Learning\Models\LearningCourse;
use App\Domains\Learning\Models\LearningCourseModule;
use App\Domains\Learning\Models\LearningCourseObjective;
use App\Domains\Learning\Models\LearningCoursePrerequisite;
use App\Domains\Learning\Models\LearningCourseVersion;
use App\Domains\Learning\Models\LearningItem;
use App\Domains\Shared\Services\AuditService;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LearningCourseService
{
    private const ALLOWED_TRANSITIONS = [
        'draft' => ['review', 'published', 'archived'],
        'review' => ['published', 'draft', 'archived'],
        'published' => ['active', 'suspended', 'archived'],
        'active' => ['suspended', 'archived', 'published'],
        'suspended' => ['active', 'archived', 'published'],
        'archived' => [],
    ];

    public function __construct(
        private readonly AuditService $audit,
        private readonly EventBus $events
    ) {}

    public function create(User $actor, array $attributes): LearningCourse
    {
        return DB::transaction(function () use ($actor, $attributes) {
            $tenantId = (string) ($attributes['tenant_id'] ?? $actor->tenant_id);

            $course = LearningCourse::query()->create([
                ...$attributes,
                'tenant_id' => $tenantId,
                'created_by' => $actor->id,
                'updated_by' => $actor->id,
                'status' => $attributes['status'] ?? 'draft',
                'current_version' => 1,
            ]);

            // Create Initial Version 1
            $version = LearningCourseVersion::query()->create([
                'tenant_id' => $tenantId,
                'course_id' => $course->id,
                'version_number' => 1,
                'title' => $course->title,
                'description' => $course->description,
                'status' => 'draft',
                'created_by' => $actor->id,
            ]);

            LearningCourseCreated::dispatch($course);
            LearningCourseVersionCreated::dispatch($version);

            $this->audit->record(
                $tenantId,
                'LearningCourseCreated',
                'create_course',
                LearningCourse::class,
                (string) $course->id,
                $actor->id,
                null,
                ['code' => $course->code, 'title' => $course->title]
            );

            return $course->load(['versions', 'objectives', 'prerequisites']);
        });
    }

    public function update(User $actor, LearningCourse $course, array $attributes): LearningCourse
    {
        return DB::transaction(function () use ($actor, $course, $attributes) {
            $before = $course->only(['title', 'description', 'status', 'difficulty', 'delivery_type']);

            $course->update([
                ...$attributes,
                'updated_by' => $actor->id,
            ]);

            $this->audit->record(
                (string) $course->tenant_id,
                'LearningCourseUpdated',
                'update_course',
                LearningCourse::class,
                (string) $course->id,
                $actor->id,
                $before,
                $course->only(['title', 'description', 'status', 'difficulty', 'delivery_type'])
            );

            return $course->fresh(['versions', 'objectives', 'prerequisites', 'modules']);
        });
    }

    public function transition(User $actor, LearningCourse $course, string $targetStatus): LearningCourse
    {
        $allowed = self::ALLOWED_TRANSITIONS[$course->status] ?? [];
        if (! in_array($targetStatus, $allowed, true)) {
            throw ValidationException::withMessages([
                'status' => "Cannot transition course from '{$course->status}' to '{$targetStatus}'."
            ]);
        }

        return DB::transaction(function () use ($actor, $course, $targetStatus) {
            $previous = $course->status;
            $course->update([
                'status' => $targetStatus,
                'updated_by' => $actor->id,
            ]);

            if ($targetStatus === 'published' || $targetStatus === 'active') {
                LearningCoursePublished::dispatch($course);
            }

            $this->audit->record(
                (string) $course->tenant_id,
                'LearningCourseStatusChanged',
                "transition_{$previous}_to_{$targetStatus}",
                LearningCourse::class,
                (string) $course->id,
                $actor->id,
                ['status' => $previous],
                ['status' => $targetStatus]
            );

            return $course;
        });
    }

    /**
     * Create a new version of the course, snapshotting existing content.
     */
    public function createVersion(User $actor, LearningCourse $course, ?string $changeLog = null): LearningCourseVersion
    {
        return DB::transaction(function () use ($actor, $course, $changeLog) {
            $nextVersionNumber = $course->current_version + 1;

            $modules = $course->modules()->with(['lessons', 'items'])->get();
            $snapshot = [
                'course' => $course->only(['title', 'description', 'duration', 'passing_score']),
                'modules' => $modules->toArray(),
            ];

            $version = LearningCourseVersion::query()->create([
                'tenant_id' => $course->tenant_id,
                'course_id' => $course->id,
                'version_number' => $nextVersionNumber,
                'title' => $course->title,
                'description' => $course->description,
                'content_snapshot' => $snapshot,
                'change_log' => $changeLog,
                'status' => 'draft',
                'created_by' => $actor->id,
            ]);

            $course->update([
                'current_version' => $nextVersionNumber,
                'updated_by' => $actor->id,
            ]);

            LearningCourseVersionCreated::dispatch($version);

            $this->audit->record(
                (string) $course->tenant_id,
                'LearningCourseVersionCreated',
                'create_version',
                LearningCourseVersion::class,
                (string) $version->id,
                $actor->id,
                null,
                ['version_number' => $nextVersionNumber, 'change_log' => $changeLog]
            );

            return $version;
        });
    }

    public function addObjective(LearningCourse $course, array $data): LearningCourseObjective
    {
        return LearningCourseObjective::query()->create([
            ...$data,
            'tenant_id' => $course->tenant_id,
            'course_id' => $course->id,
        ]);
    }

    public function addPrerequisite(LearningCourse $course, array $data): LearningCoursePrerequisite
    {
        return LearningCoursePrerequisite::query()->create([
            ...$data,
            'tenant_id' => $course->tenant_id,
            'course_id' => $course->id,
        ]);
    }

    public function addModule(LearningCourse $course, array $data): LearningCourseModule
    {
        return LearningCourseModule::query()->create([
            ...$data,
            'tenant_id' => $course->tenant_id,
            'course_id' => $course->id,
        ]);
    }
}
