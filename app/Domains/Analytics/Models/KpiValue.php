<?php
namespace App\Domains\Analytics\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
class KpiValue extends Model
{
    use HasUuids;
    protected $table = 'kpi_values';
    protected $fillable = ['tenant_id', 'kpi_definition_id', 'period_start', 'period_end', 'value', 'dimensions', 'calculated_at'];
    protected function casts(): array { return ['value' => 'float', 'dimensions' => 'array', 'period_start' => 'date', 'period_end' => 'date', 'calculated_at' => 'datetime']; }
}
