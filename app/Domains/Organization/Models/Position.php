<?php

namespace App\Domains\Organization\Models;

class Position extends OrganizationModel
{
    protected $fillable = ['tenant_id','company_id','business_unit_id','division_id','department_id','team_id','branch_id','location_id','cost_center_id','profit_center_id','job_id','job_grade_id','parent_position_id','code','position_code','title','title_override','headcount','filled_headcount','status','effective_from','effective_to','version'];
    protected function casts(): array { return ['headcount' => 'integer', 'filled_headcount' => 'integer', 'effective_from' => 'date', 'effective_to' => 'date']; }
    public function vacancies(): int { return max(0, $this->headcount - $this->filled_headcount); }
}
