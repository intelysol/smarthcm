<?php

namespace App\Domains\Performance\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;

class PerformanceCycle extends PerformanceModel
{
    use SoftDeletes;
    protected $fillable = ['tenant_id', 'name', 'description', 'cycle_type', 'start_date', 'end_date', 'self_review_start', 'self_review_end', 'manager_review_start', 'manager_review_end', 'calibration_start', 'calibration_end', 'finalization_date', 'status', 'version', 'created_by', 'updated_by'];
    protected function casts(): array { return ['start_date' => 'date', 'end_date' => 'date', 'self_review_start' => 'date', 'self_review_end' => 'date', 'manager_review_start' => 'date', 'manager_review_end' => 'date', 'calibration_start' => 'date', 'calibration_end' => 'date', 'finalization_date' => 'date', 'version' => 'integer']; }
    protected static function booted(): void { static::creating(function (self $cycle): void { $cycle->uuid ??= (string) Str::uuid(); }); }
    public function configuration(): HasOne { return $this->hasOne(PerformanceCycleConfiguration::class, 'cycle_id'); }
    public function participants(): HasMany { return $this->hasMany(PerformanceCycleParticipant::class, 'cycle_id'); }
    public function goals(): HasMany { return $this->hasMany(PerformanceGoal::class, 'cycle_id'); }
}
