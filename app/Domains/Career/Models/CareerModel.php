<?php

namespace App\Domains\Career\Models;

use App\Domains\Platform\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

abstract class CareerModel extends Model
{
    use BelongsToTenant, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;
}
