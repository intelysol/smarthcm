<?php

namespace App\Domains\Workflow\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

abstract class WorkflowModel extends Model
{
    use HasUuids;
}
