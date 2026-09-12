<?php
namespace App\Domains\Analytics\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
class AnalyticsDashboard extends Model
{
    use HasUuids;
    protected $table = 'analytics_dashboards';
    protected $fillable = ['tenant_id', 'name', 'audience', 'layout', 'security_rules', 'created_by'];
    protected function casts(): array { return ['layout' => 'array', 'security_rules' => 'array']; }
}
