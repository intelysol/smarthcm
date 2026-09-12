<?php

namespace App\Domains\Career\Jobs;

use App\Domains\Career\Models\CareerDevelopmentRecommendation;
use App\Domains\Career\Models\CareerSkillGap;
use App\Domains\Learning\Models\LearningCourse;
use App\Domains\Platform\Contracts\TenantContext;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessCareerRecommendationsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly string $tenantId,
        public readonly ?string $employeeId = null
    ) {}

    public function handle(?TenantContext $tenantContext = null): void
    {
        $tenant = Tenant::query()->find($this->tenantId);
        if (! $tenant) return;
        if ($tenantContext && ! $tenantContext->id()) {
            $tenantContext->set($tenant);
        }

        $query = CareerSkillGap::query()->where('tenant_id', $this->tenantId)->where('status', 'open');
        if ($this->employeeId) {
            $query->where('employee_id', $this->employeeId);
        }

        $gaps = $query->with('skill')->limit(50)->get();

        foreach ($gaps as $gap) {
            $course = LearningCourse::query()
                ->where('tenant_id', $this->tenantId)
                ->where('status', 'published')
                ->first();

            if ($course) {
                CareerDevelopmentRecommendation::query()->firstOrCreate(
                    [
                        'tenant_id' => $this->tenantId,
                        'employee_id' => $gap->employee_id,
                        'gap_type' => 'skill_gap',
                        'gap_id' => $gap->id,
                    ],
                    [
                        'recommendation_type' => 'learning_course',
                        'recommended_item_id' => $course->id,
                        'title' => "Recommended Course for {$gap->skill?->name}",
                        'description' => "Complete '{$course->title}' to bridge your {$gap->skill?->name} gap.",
                        'priority' => $gap->priority,
                        'status' => 'suggested',
                    ]
                );
            }
        }
    }
}
