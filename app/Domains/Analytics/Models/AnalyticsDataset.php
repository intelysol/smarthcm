<?php
namespace App\Domains\Analytics\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
class AnalyticsDataset extends Model
{
    use HasUuids;
    protected $table = 'analytics_datasets';
    protected $fillable = ['tenant_id', 'name', 'source_type', 'definition', 'security_rules'];
    protected function casts(): array { return ['definition' => 'array', 'security_rules' => 'array']; }
}
