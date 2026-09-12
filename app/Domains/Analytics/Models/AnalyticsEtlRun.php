<?php
namespace App\Domains\Analytics\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
class AnalyticsEtlRun extends Model
{
    use HasUuids;
    protected $table = 'analytics_etl_runs';
    protected $fillable = ['tenant_id', 'pipeline', 'run_type', 'status', 'records_processed', 'checkpoint', 'error_message', 'started_at', 'completed_at'];
    protected function casts(): array { return ['checkpoint' => 'array', 'started_at' => 'datetime', 'completed_at' => 'datetime']; }
}
