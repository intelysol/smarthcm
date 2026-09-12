<?php

namespace App\Domains\Learning\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['tenant_id', 'name', 'type', 'contact', 'email', 'website', 'description', 'status'])]
class LearningProvider extends LearningModel
{
    use SoftDeletes;

    /** @return HasMany<LearningCourse> */
    public function courses(): HasMany
    {
        return $this->hasMany(LearningCourse::class, 'provider_id');
    }

    /** @return HasMany<LearningInstructor> */
    public function instructors(): HasMany
    {
        return $this->hasMany(LearningInstructor::class, 'provider_id');
    }

    /** @return HasMany<LearningSession> */
    public function sessions(): HasMany
    {
        return $this->hasMany(LearningSession::class, 'provider_id');
    }
}
