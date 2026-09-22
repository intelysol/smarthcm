<?php

namespace App\Domains\AiOperations\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HcmAiEvalDataset extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'hcm_ai_eval_datasets';

    protected $fillable = [
        'tenant_id',
        'dataset_code',
        'name',
        'description',
        'version',
        'dataset_type',
        'total_cases',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'total_cases' => 'integer',
    ];

    public function cases()
    {
        return $this->hasMany(HcmAiEvalCase::class, 'dataset_id');
    }

    public function runs()
    {
        return $this->hasMany(HcmAiEvalRun::class, 'dataset_id');
    }
}
