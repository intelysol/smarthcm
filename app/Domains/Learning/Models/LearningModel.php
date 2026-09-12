<?php

namespace App\Domains\Learning\Models;

use App\Domains\Platform\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

abstract class LearningModel extends Model
{
    use BelongsToTenant, HasUuids;
}
