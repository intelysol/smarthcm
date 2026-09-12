<?php

namespace App\Domains\Career\Services;

use App\Domains\Career\Events\SkillGapDetected;
use App\Domains\Career\Events\SkillGapResolved;
use App\Domains\Career\Models\CareerJobSkillRequirement;
use App\Domains\Career\Models\CareerSkillGap;
use App\Domains\Career\Models\EmployeeSkill;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Job;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CareerSkillGapService
{
    public function analyzeGapsForTargetJob(Employee $employee, Job $targetJob): Collection
    {
        $requirements = CareerJobSkillRequirement::query()
            ->where('tenant_id', $employee->tenant_id)
            ->where('job_id', $targetJob->id)
            ->get();

        $employeeSkills = EmployeeSkill::query()
            ->where('tenant_id', $employee->tenant_id)
            ->where('employee_id', $employee->id)
            ->pluck('current_level', 'skill_id');

        $detectedGaps = collect();

        DB::transaction(function () use ($employee, $targetJob, $requirements, $employeeSkills, &$detectedGaps) {
            foreach ($requirements as $req) {
                $currentLevel = (int) ($employeeSkills[$req->skill_id] ?? 0);
                $requiredLevel = (int) $req->required_level;
                $gap = max(0, $requiredLevel - $currentLevel);

                if ($gap > 0) {
                    $priority = match ($req->importance) {
                        'critical' => 'critical',
                        'high' => ($gap >= 2) ? 'critical' : 'high',
                        'medium' => ($gap >= 2) ? 'high' : 'medium',
                        default => 'low',
                    };

                    $gapRecord = CareerSkillGap::query()->updateOrCreate(
                        [
                            'tenant_id' => $employee->tenant_id,
                            'employee_id' => $employee->id,
                            'skill_id' => $req->skill_id,
                            'target_job_id' => $targetJob->id,
                        ],
                        [
                            'current_level' => $currentLevel,
                            'required_level' => $requiredLevel,
                            'gap' => $gap,
                            'priority' => $priority,
                            'source' => 'target_job',
                            'status' => 'open',
                        ]
                    );

                    SkillGapDetected::dispatch($gapRecord);
                    $detectedGaps->push($gapRecord);
                } else {
                    // Check if existing gap was resolved
                    $existing = CareerSkillGap::query()
                        ->where('tenant_id', $employee->tenant_id)
                        ->where('employee_id', $employee->id)
                        ->where('skill_id', $req->skill_id)
                        ->where('target_job_id', $targetJob->id)
                        ->first();

                    if ($existing && $existing->status !== 'resolved') {
                        $existing->update(['status' => 'resolved', 'gap' => 0, 'current_level' => $currentLevel]);
                        SkillGapResolved::dispatch($existing);
                    }
                }
            }
        });

        return $detectedGaps;
    }

    public function calculateSkillMatchPercentage(Employee $employee, Job $targetJob): float
    {
        $requirements = CareerJobSkillRequirement::query()
            ->where('tenant_id', $employee->tenant_id)
            ->where('job_id', $targetJob->id)
            ->get();

        if ($requirements->isEmpty()) {
            return 100.0;
        }

        $employeeSkills = EmployeeSkill::query()
            ->where('tenant_id', $employee->tenant_id)
            ->where('employee_id', $employee->id)
            ->pluck('current_level', 'skill_id');

        $totalRequiredPoints = 0;
        $earnedPoints = 0;

        foreach ($requirements as $req) {
            $currentLevel = (int) ($employeeSkills[$req->skill_id] ?? 0);
            $requiredLevel = (int) $req->required_level;
            $weight = ($req->importance === 'critical') ? 3 : (($req->importance === 'high') ? 2 : 1);

            $totalRequiredPoints += ($requiredLevel * $weight);
            $earnedPoints += (min($currentLevel, $requiredLevel) * $weight);
        }

        if ($totalRequiredPoints === 0) {
            return 100.0;
        }

        return round(($earnedPoints / $totalRequiredPoints) * 100.0, 2);
    }
}
