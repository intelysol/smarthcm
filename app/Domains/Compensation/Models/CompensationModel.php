<?php

namespace App\Domains\Compensation\Models;

use App\Domains\Platform\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

abstract class CompensationModel extends Model
{
    use BelongsToTenant, HasUuids;
}
