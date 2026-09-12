<?php

namespace App\Domains\Organization\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Team extends OrganizationModel
{
    protected $fillable = ['tenant_id', 'section_id', 'team_name', 'team_lead_id', 'description', 'created_by', 'updated_by', 'deleted_by'];

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function teamLead(): BelongsTo
    {
        return $this->belongsTo(User::class, 'team_lead_id');
    }
}
