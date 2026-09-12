<?php
namespace App\Domains\Operations\Services;
use App\Domains\Operations\Models\{OpsAlert, OpsAlertRule, OpsIncident, OpsMetric};
class OperationsService
{
    public function metric(?string $tenantId, string $name, float $value, ?string $unit = null, array $dimensions = []): OpsMetric { return OpsMetric::query()->create(['tenant_id'=>$tenantId,'metric'=>$name,'value'=>$value,'unit'=>$unit,'dimensions'=>$dimensions,'recorded_at'=>now()]); }
    public function evaluate(?string $tenantId): int
    {
        $count = 0; $rules = OpsAlertRule::query()->where('is_active', true)->where(fn ($q) => $q->whereNull('tenant_id')->orWhere('tenant_id', $tenantId))->get();
        foreach ($rules as $rule) { $metric = OpsMetric::query()->where('metric', $rule->metric)->where(fn ($q) => $q->whereNull('tenant_id')->orWhere('tenant_id', $tenantId))->latest('recorded_at')->first(); if (! $metric) continue; $matched = match ($rule->operator) { '>' => $metric->value > $rule->threshold, '>=' => $metric->value >= $rule->threshold, '<' => $metric->value < $rule->threshold, '<=' => $metric->value <= $rule->threshold, '=' => $metric->value == $rule->threshold, default => false }; if ($matched) { OpsAlert::query()->create(['tenant_id'=>$tenantId,'rule_id'=>$rule->id,'severity'=>$rule->severity,'message'=>"{$rule->metric} breached {$rule->threshold}",'payload'=>['value'=>$metric->value],'triggered_at'=>now()]); $count++; } }
        return $count;
    }
    public function resolveIncident(OpsIncident $incident, string $rootCause): OpsIncident { $incident->update(['status'=>'resolved','root_cause'=>$rootCause,'resolved_at'=>now()]); return $incident; }
}
