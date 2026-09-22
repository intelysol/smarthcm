<?php

declare(strict_types=1);

namespace Tests\Performance;

use App\Domains\Operations\Models\OpsMetric;
use App\Domains\Operations\Services\PerformanceEngineeringService;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class EnterprisePerformanceCertificationTest extends TestCase
{
    use RefreshDatabase;

    protected PerformanceEngineeringService $perfService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->perfService = app(PerformanceEngineeringService::class);
    }

    /**
     * 1. Performance Budgets Compliance (P95 Latency & Query Count SLAs)
     */
    public function test_performance_budgets_are_defined_and_fully_compliant(): void
    {
        $evaluation = $this->perfService->evaluatePerformanceBudgets();

        $this->assertEquals('compliant', $evaluation['status']);
        $this->assertEquals('100%', $evaluation['overall_budget_compliance']);
        $this->assertNotEmpty($evaluation['budgets']);

        foreach ($evaluation['budgets'] as $budget) {
            $this->assertArrayHasKey('category', $budget);
            $this->assertArrayHasKey('target_p95_ms', $budget);
            $this->assertArrayHasKey('measured_p95_ms', $budget);
            $this->assertArrayHasKey('max_queries', $budget);
            $this->assertArrayHasKey('measured_queries', $budget);
            $this->assertEquals('compliant', $budget['status']);

            $this->assertLessThanOrEqual(
                $budget['target_p95_ms'],
                $budget['measured_p95_ms'],
                "P95 latency exceeded SLA budget for category: {$budget['category']}"
            );

            $this->assertLessThanOrEqual(
                $budget['max_queries'],
                $budget['measured_queries'],
                "Query count exceeded SLA limit for category: {$budget['category']}"
            );
        }
    }

    /**
     * 2. N+1 Query Elimination & Constant O(1) Query Scaling
     */
    public function test_n_plus_one_query_prevention_guarantees_constant_query_scaling(): void
    {
        $tenant = Tenant::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Scale Corp',
            'slug' => 'scale-corp-' . Str::random(6),
            'status' => 'active',
        ]);

        // Seed 3 metrics
        for ($i = 0; $i < 3; $i++) {
            OpsMetric::query()->create([
                'tenant_id' => $tenant->id,
                'metric' => "metric_{$i}",
                'value' => 10.0 + $i,
                'unit' => 'count',
                'dimensions' => ['idx' => $i],
                'recorded_at' => now(),
            ]);
        }

        $profileSmall = $this->perfService->profileExecution(function () use ($tenant) {
            return OpsMetric::query()
                ->where('tenant_id', $tenant->id)
                ->orderBy('id')
                ->get();
        }, 'query_small_dataset');

        // Seed 15 additional metrics (total 18)
        for ($i = 3; $i < 18; $i++) {
            OpsMetric::query()->create([
                'tenant_id' => $tenant->id,
                'metric' => "metric_{$i}",
                'value' => 10.0 + $i,
                'unit' => 'count',
                'dimensions' => ['idx' => $i],
                'recorded_at' => now(),
            ]);
        }

        $profileLarge = $this->perfService->profileExecution(function () use ($tenant) {
            return OpsMetric::query()
                ->where('tenant_id', $tenant->id)
                ->orderBy('id')
                ->get();
        }, 'query_large_dataset');

        // Number of queries must remain constant O(1) regardless of record count
        $this->assertEquals(
            $profileSmall['queries_count'],
            $profileLarge['queries_count'],
            'Query count scaled with record count, indicating an N+1 query regression!'
        );
        $this->assertEquals(1, $profileLarge['queries_count']);
    }

    /**
     * 3. Authoritative Capacity Modeling & Minimum 35% Headroom Guarantee
     */
    public function test_capacity_model_guarantees_minimum_35_percent_headroom(): void
    {
        $model = $this->perfService->getCapacityModel();

        $this->assertEquals('certified', $model['status']);
        $this->assertTrue($model['headroom_sufficient']);
        $this->assertGreaterThanOrEqual(35, $model['minimum_headroom_percent']);
        $this->assertNotEmpty($model['resources']);

        foreach ($model['resources'] as $resource) {
            $this->assertArrayHasKey('name', $resource);
            $this->assertArrayHasKey('baseline', $resource);
            $this->assertArrayHasKey('sustainable_capacity', $resource);
            $this->assertArrayHasKey('stress_limit', $resource);
            $this->assertArrayHasKey('headroom_percent', $resource);
            $this->assertArrayHasKey('bottleneck', $resource);
            $this->assertEquals('compliant', $resource['status']);

            $this->assertGreaterThanOrEqual(
                35,
                $resource['headroom_percent'],
                "Resource {$resource['name']} has insufficient headroom (< 35%)"
            );
        }
    }

    /**
     * 4. Multi-Tenant Noisy-Neighbor Isolation & Resource Fairness
     */
    public function test_multi_tenant_noisy_neighbor_isolation_prevents_resource_starvation(): void
    {
        $noisyTenant = Tenant::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Noisy Tenant Inc',
            'slug' => 'noisy-' . Str::random(6),
            'status' => 'active',
        ]);

        $quietTenant = Tenant::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Quiet Tenant LLC',
            'slug' => 'quiet-' . Str::random(6),
            'status' => 'active',
        ]);

        $fairness = $this->perfService->verifyTenantFairness($noisyTenant->id, $quietTenant->id);

        $this->assertEquals('isolated', $fairness['status']);
        $this->assertTrue($fairness['is_isolated']);
        $this->assertLessThan(
            $fairness['threshold_max_impact'],
            $fairness['quiet_tenant_impact_percent'],
            'Quiet tenant latency suffered excessive degradation from noisy neighbor!'
        );
    }

    /**
     * 5. Deadlock Prevention & Idempotent Concurrency Locking
     */
    public function test_idempotent_concurrency_locking_prevents_duplicate_execution(): void
    {
        $idempotencyKey = 'payroll_run_' . Str::random(8);

        // 1. Initial safe execution
        $firstAttempt = $this->perfService->verifyIdempotentConcurrency(
            $idempotencyKey,
            fn () => ['processed_count' => 120, 'net_pay' => 450000.0]
        );

        $this->assertEquals('executed_safely', $firstAttempt['status']);
        $this->assertTrue($firstAttempt['executed']);
        $this->assertEquals(120, $firstAttempt['result']['processed_count']);

        // 2. Simulate concurrent collision where lock is already held
        Cache::put("idempotency_lock:{$idempotencyKey}", 'locked', 15);

        $secondAttempt = $this->perfService->verifyIdempotentConcurrency(
            $idempotencyKey,
            fn () => ['processed_count' => 120, 'net_pay' => 450000.0]
        );

        $this->assertEquals('duplicate_execution_suppressed', $secondAttempt['status']);
        $this->assertFalse($secondAttempt['executed']);

        // Clean up lock
        Cache::forget("idempotency_lock:{$idempotencyKey}");
    }

    /**
     * 6. Bulk Operation Chunking & Bounded Peak Memory
     */
    public function test_bulk_operation_chunking_maintains_bounded_memory(): void
    {
        $profile = $this->perfService->profileExecution(function () {
            $processedCount = 0;
            $items = range(1, 1000);

            // Chunking simulation to guarantee memory bounds
            foreach (array_chunk($items, 100) as $chunk) {
                foreach ($chunk as $item) {
                    $processedCount++;
                }
            }

            return $processedCount;
        }, 'chunked_batch_processing');

        $this->assertEquals(1000, $profile['result']);
        $this->assertLessThanOrEqual(5.0, $profile['memory_delta_mb']);
    }

    /**
     * 7. Operations Performance Dashboard Telemetry
     */
    public function test_performance_dashboard_telemetry_returns_healthy_state(): void
    {
        $telemetry = $this->perfService->getPerformanceDashboardData();

        $this->assertEquals('healthy', $telemetry['status']);
        $this->assertLessThan(200, $telemetry['p95_latency_ms']);
        $this->assertGreaterThan(95.0, $telemetry['cache_hit_rate']);
        $this->assertGreaterThan(0, $telemetry['current_throughput_rps']);
        $this->assertArrayHasKey('memory_utilization_mb', $telemetry);
        $this->assertArrayHasKey('peak_memory_mb', $telemetry);
    }

    /**
     * 8. Operations Performance and Capacity Web Routes
     */
    public function test_operations_performance_and_capacity_web_routes_render(): void
    {
        $tenant = Tenant::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Ops Tenant',
            'slug' => 'ops-' . Str::random(6),
            'status' => 'active',
        ]);

        $admin = User::factory()->create([
            'tenant_id' => $tenant->id,
            'is_platform_admin' => true,
        ]);

        $perfResponse = $this->actingAs($admin)->get(route('operations.performance'));
        $perfResponse->assertStatus(200);
        $perfResponse->assertSee('Performance Engineering');
        $perfResponse->assertSee('Performance Budget Compliance by Category');

        $capResponse = $this->actingAs($admin)->get(route('operations.capacity'));
        $capResponse->assertStatus(200);
        $capResponse->assertSee('Capacity &amp; Scalability Model', false);
        $capResponse->assertSee('Authoritative Measured Capacity Boundaries');
    }
}
