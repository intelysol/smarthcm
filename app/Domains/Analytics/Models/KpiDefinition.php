<?php
namespace App\Domains\Analytics\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class KpiDefinition extends Model
{
    use HasUuids;
    protected $table = 'kpi_definitions';
    protected $fillable = ['tenant_id', 'code', 'name', 'category', 'calculation_type', 'definition', 'unit', 'frequency', 'is_active'];
    protected function casts(): array { return ['definition' => 'array', 'is_active' => 'boolean']; }
    public function values(): HasMany { return $this->hasMany(KpiValue::class, 'kpi_definition_id'); }
}
