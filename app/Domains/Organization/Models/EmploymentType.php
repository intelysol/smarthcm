<?php

namespace App\Domains\Organization\Models;

class EmploymentType extends OrganizationModel
{
    protected $fillable = ['tenant_id', 'name', 'description', 'status', 'created_by', 'updated_by', 'deleted_by'];
}
