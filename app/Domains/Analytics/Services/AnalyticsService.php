<?php
namespace App\Domains\Analytics\Services;
use App\Domains\Analytics\Models\{AnalyticsEtlRun, AnalyticsFact, KpiDefinition, KpiValue};
use Illuminate\Support\Carbon;
class AnalyticsService
{
    public function calculate(string $tenantId, KpiDefinition $kpi, string $from, string $to): KpiValue
    {
        $measure = $kpi->definition['measure'] ?? 'value'; $query = AnalyticsFact::query()->where('tenant_id', $tenantId)->whereBetween('fact_date', [$from, $to]);
        if (! empty($kpi->definition['fact_type'])) $query->where('fact_type', $kpi->definition['fact_type']);
        $values = $query->get()->map(fn (AnalyticsFact $fact): float => (float) ($fact->measures[$measure] ?? 0));
        $value = match ($kpi->calculation_type) { 'count' => $values->count(), 'average', 'avg' => $values->avg() ?: 0, 'min' => $values->min() ?: 0, 'max' => $values->max() ?: 0, default => $values->sum() };
        return KpiValue::query()->updateOrCreate(['tenant_id' => $tenantId, 'kpi_definition_id' => $kpi->id, 'period_start' => $from, 'period_end' => $to], ['value' => $value, 'calculated_at' => now()]);
    }
    public function ingest(string $tenantId, array $facts): int
    {
        foreach ($facts as $fact) AnalyticsFact::query()->create([...$fact, 'tenant_id' => $tenantId]);
        return count($facts);
    }
    /** @return array{history:list<float>, forecast:float, trend:string} */
    public function forecast(string $tenantId, KpiDefinition $kpi, int $periods = 3): array
    {
        $history = $kpi->values()->where('tenant_id', $tenantId)->latest('period_end')->limit(12)->pluck('value')->reverse()->map(fn ($value): float => (float) $value)->values()->all();
        $average = count($history) ? array_sum($history) / count($history) : 0.0; $trend = count($history) > 1 && end($history) >= $history[0] ? 'up' : 'down';
        return ['history' => $history, 'forecast' => round($average, 6), 'trend' => $trend, 'periods' => $periods];
    }
    public function refresh(string $tenantId, string $pipeline, string $runType = 'manual'): AnalyticsEtlRun
    { return AnalyticsEtlRun::query()->create(['tenant_id' => $tenantId, 'pipeline' => $pipeline, 'run_type' => $runType, 'status' => 'queued', 'started_at' => now()]); }
}
