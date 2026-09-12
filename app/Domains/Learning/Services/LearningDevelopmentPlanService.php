<?php

namespace App\Domains\Learning\Services;

use App\Domains\Learning\Models\LearningDevelopmentActivity;
use App\Domains\Learning\Models\LearningDevelopmentPlan;
use Illuminate\Support\Facades\DB;

class LearningDevelopmentPlanService
{
    public function createPlan(array $data): LearningDevelopmentPlan
    {
        return DB::transaction(function () use ($data) {
            $plan = LearningDevelopmentPlan::create([
                'tenant_id' => $data['tenant_id'],
                'employee_id' => $data['employee_id'],
                'title' => $data['title'],
                'goal' => $data['goal'],
                'skill_target' => $data['skill_target'] ?? null,
                'competency_target' => $data['competency_target'] ?? null,
                'target_level' => $data['target_level'] ?? 'intermediate',
                'target_completion_date' => $data['target_completion_date'],
                'status' => 'active',
                'manager_id' => $data['manager_id'] ?? null,
                'mentor_id' => $data['mentor_id'] ?? null,
            ]);

            if (!empty($data['activities'])) {
                foreach ($data['activities'] as $act) {
                    $this->addActivity($plan, $act);
                }
            }

            return $plan;
        });
    }

    public function addActivity(LearningDevelopmentPlan $plan, array $activityData): LearningDevelopmentActivity
    {
        return LearningDevelopmentActivity::create([
            'tenant_id' => $plan->tenant_id,
            'development_plan_id' => $plan->id,
            'activity_type' => $activityData['activity_type'] ?? 'course',
            'title' => $activityData['title'],
            'description' => $activityData['description'] ?? null,
            'course_id' => $activityData['course_id'] ?? null,
            'target_date' => $activityData['target_date'] ?? null,
            'status' => 'planned',
        ]);
    }

    public function completeActivity(LearningDevelopmentActivity $activity, ?string $evidenceNotes = null): LearningDevelopmentActivity
    {
        $activity->update([
            'status' => 'completed',
            'completed_at' => now(),
            'evidence_notes' => $evidenceNotes,
        ]);

        // Auto-check if all activities in plan are complete
        $plan = $activity->developmentPlan;
        if ($plan && $plan->activities()->where('status', '!=', 'completed')->count() === 0) {
            $plan->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
        }

        return $activity;
    }
}
