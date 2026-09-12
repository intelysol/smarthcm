<?php

namespace App\Domains\Employee\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

abstract class EmployeeChildModel extends EmployeeModel
{
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
