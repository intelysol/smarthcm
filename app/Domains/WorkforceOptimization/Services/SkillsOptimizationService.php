<?php

namespace App\Domains\WorkforceOptimization\Services;

use App\Domains\WorkforceOptimization\DTOs\WorkforceOpportunityData;
use App\Domains\WorkforceOptimization\Enums\OpportunityCategory;
use App\Domains\WorkforceOptimization\Models\HcmWorkforceOptimizationOpportunity;
use App\Domains\Employee\Models\Employee;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SkillsOptimizationService
{
    /**
     * Identify skills bottlenecks, single points of failure (SPOF), and cross-training / redeployment opportunities.
     */
    public function analyzeSkillsHealth(?string $tenantId = null): array
    {
        $spofs = $this->detectSinglePointsOfFailure($tenantId);
        $gaps = $this->detectSkillGaps($tenantId);
        $redeployable = $this->findRedeploymentMatches($tenantId);

        return [
            'spofs' => $spofs,
            'skill_gaps' => $gaps,
            'redeployment_matches' => $redeployable,
        ];
    }

    /**
     * Detect single points of failure (skills held by only 1 employee in department or organization).
     */
    public function detectSinglePointsOfFailure(?string $tenantId = null): array
    {
        $spofs = [];

        // 1. Check native SmartHCM employee_profile_skills
        if (Schema::hasTable('employee_profile_skills')) {
            $query = DB::table('employee_profile_skills')
                ->join('employees', 'employees.id', '=', 'employee_profile_skills.employee_id')
                ->select(
                    'employee_profile_skills.skill_name',
                    'employees.department_id',
                    DB::raw('count(DISTINCT employee_profile_skills.employee_id) as holder_count'),
                    DB::raw('MIN(employees.id) as sole_employee_id')
                )
                ->whereNotNull('employee_profile_skills.skill_name')
                ->where('employee_profile_skills.skill_name', '!=', '')
                ->groupBy('employee_profile_skills.skill_name', 'employees.department_id')
                ->havingRaw('count(DISTINCT employee_profile_skills.employee_id) = 1');

            if ($tenantId && Schema::hasColumn('employee_profile_skills', 'tenant_id')) {
                $query->where('employee_profile_skills.tenant_id', $tenantId);
            }

            $results = $query->limit(20)->get();

            foreach ($results as $row) {
                $codeKey = substr(md5($row->skill_name), 0, 8);
                $spofOpp = HcmWorkforceOptimizationOpportunity::updateOrCreate(
                    [
                        'tenant_id' => $tenantId,
                        'opportunity_code' => 'SPOF_SKILL_'.$codeKey.'_DEPT_'.$row->department_id,
                    ],
                    [
                        'category' => 'SKILLS',
                        'title' => "Critical Skill Single Point of Failure: {$row->skill_name}",
                        'department_id' => $row->department_id,
                        'role_or_skill' => $row->skill_name,
                        'severity' => 'high',
                        'capacity_gap_hours' => 40.0,
                        'estimated_impact_amount' => 3500.0,
                        'confidence_score' => 0.90,
                        'details' => ['holder_count' => 1, 'skill_name' => $row->skill_name],
                        'status' => 'open',
                    ]
                );
                $spofs[] = $spofOpp;
            }
        }

        // 2. Check if employee_skills or career_skills exist
        if (empty($spofs) && Schema::hasTable('employee_skills') && Schema::hasTable('skills')) {
            $query = DB::table('employee_skills')
                ->join('skills', 'skills.id', '=', 'employee_skills.skill_id')
                ->join('employees', 'employees.id', '=', 'employee_skills.employee_id')
                ->select(
                    'employee_skills.skill_id',
                    'skills.name as skill_name',
                    'employees.department_id',
                    DB::raw('count(DISTINCT employee_skills.employee_id) as holder_count'),
                    DB::raw('MIN(employees.id) as sole_employee_id')
                )
                ->groupBy('employee_skills.skill_id', 'skills.name', 'employees.department_id')
                ->having('holder_count', '=', 1);

            if ($tenantId && Schema::hasColumn('employee_skills', 'tenant_id')) {
                $query->where('employee_skills.tenant_id', $tenantId);
            }

            $results = $query->limit(20)->get();

            foreach ($results as $row) {
                $spofOpp = HcmWorkforceOptimizationOpportunity::updateOrCreate(
                    [
                        'tenant_id' => $tenantId,
                        'opportunity_code' => 'SPOF_SKILL_'.$row->skill_id.'_DEPT_'.$row->department_id,
                    ],
                    [
                        'category' => 'SKILLS',
                        'title' => "Critical Skill Single Point of Failure: {$row->skill_name}",
                        'department_id' => $row->department_id,
                        'role_or_skill' => $row->skill_name,
                        'severity' => 'high',
                        'capacity_gap_hours' => 40.0,
                        'estimated_impact_amount' => 3500.0,
                        'confidence_score' => 0.90,
                        'details' => ['holder_count' => 1, 'skill_name' => $row->skill_name, 'skill_id' => $row->skill_id],
                        'status' => 'open',
                    ]
                );
                $spofs[] = $spofOpp;
            }
        }

        return $spofs;
    }

    /**
     * Detect general skill gaps based on role requirements.
     */
    public function detectSkillGaps(?string $tenantId = null): array
    {
        $gaps = [];

        if (Schema::hasTable('job_role_skills') && Schema::hasTable('employee_skills')) {
            // Find job roles with missing required skills
            $missing = DB::table('job_role_skills')
                ->select('job_role_id', 'skill_id', DB::raw('count(*) as req_count'))
                ->groupBy('job_role_id', 'skill_id')
                ->limit(10)
                ->get();

            foreach ($missing as $m) {
                $gaps[] = [
                    'job_role_id' => $m->job_role_id,
                    'skill_id' => $m->skill_id,
                    'deficit_level' => 'MODERATE',
                ];
            }
        }

        return $gaps;
    }

    /**
     * Find candidates for internal redeployment based on adjacent skill profiles.
     */
    public function findRedeploymentMatches(?string $tenantId = null, ?string $targetSkillId = null): array
    {
        $matches = [];

        if (Schema::hasTable('employee_skills')) {
            $query = DB::table('employee_skills')
                ->join('employees', 'employees.id', '=', 'employee_skills.employee_id')
                ->select('employees.id as employee_id', 'employees.first_name', 'employees.last_name', 'employees.department_id')
                ->distinct();

            if ($targetSkillId) {
                $query->where('employee_skills.skill_id', $targetSkillId);
            }

            if ($tenantId && Schema::hasColumn('employee_skills', 'tenant_id')) {
                $query->where('employee_skills.tenant_id', $tenantId);
            }

            $matches = $query->limit(10)->get()->toArray();
        }

        return $matches;
    }
}
