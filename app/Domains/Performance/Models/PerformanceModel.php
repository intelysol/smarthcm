<?php

namespace App\Domains\Performance\Models;

use App\Domains\Platform\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

abstract class PerformanceModel extends Model
{
    use BelongsToTenant, HasUuids;
}
