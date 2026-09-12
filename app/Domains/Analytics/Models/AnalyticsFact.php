<?php
namespace App\Domains\Analytics\Models;
use Illuminate\Database\Eloquent\Model;
class AnalyticsFact extends Model
{
    protected $table = 'analytics_facts';
    protected $fillable = ['tenant_id', 'fact_type', 'fact_date', 'subject_type', 'subject_id', 'dimensions', 'measures', 'source_updated_at'];
    protected function casts(): array { return ['fact_date' => 'date', 'dimensions' => 'array', 'measures' => 'array', 'source_updated_at' => 'datetime']; }
}
