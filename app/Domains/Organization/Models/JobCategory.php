<?php

namespace App\Domains\Organization\Models;

class JobCategory extends OrganizationModel
{
    protected $fillable = ['tenant_id', 'category_name', 'description', 'created_by', 'updated_by', 'deleted_by'];
}
