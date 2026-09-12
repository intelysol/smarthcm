<?php
namespace App\Domains\Analytics\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
class AnalyticsReport extends Model
{
    use HasUuids;
    protected $table = 'analytics_reports';
    protected $fillable = ['tenant_id', 'dataset_id', 'name', 'definition', 'schedule', 'delivery'];
    protected function casts(): array { return ['definition' => 'array', 'schedule' => 'array', 'delivery' => 'array']; }
}
