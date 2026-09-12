<?php

namespace App\Domains\Employee\Models;

class Person extends EmployeeModel
{
    protected $table = 'people';
    protected $fillable = ['tenant_id','person_number','first_name','middle_name','last_name','preferred_name','display_name','gender','date_of_birth','nationality_code','status','version'];
    protected function casts(): array { return ['date_of_birth' => 'date']; }
}
