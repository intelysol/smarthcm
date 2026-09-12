<?php

namespace App\Domains\Organization\Models;

use App\Domains\Career\Models\CareerJobSkillRequirement;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Job extends OrganizationModel
{
    protected $table = 'organization_job_definitions';
    protected $fillable = ['tenant_id','job_family_id','job_code','title','description','skills','minimum_experience','status','effective_from','effective_to'];
    protected function casts(): array { return ['skills' => 'array', 'effective_from' => 'date', 'effective_to' => 'date']; }

    /** @return HasMany<CareerJobSkillRequirement> */
    public function skillRequirements(): HasMany
    {
        return $this->hasMany(CareerJobSkillRequirement::class, 'job_id');
    }
}
