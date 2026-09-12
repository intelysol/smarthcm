<?php

namespace App\Domains\Employee\Models;

class Employment extends EmployeeModel
{
    protected $fillable = ['tenant_id','employee_id','company_id','employment_type_id','job_id','job_grade_id','position_id','department_id','manager_employee_id','employment_number','start_date','end_date','status','effective_from','effective_to','version'];
    protected function casts(): array { return ['start_date' => 'date','end_date' => 'date','effective_from' => 'date','effective_to' => 'date']; }

    public function employee(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
