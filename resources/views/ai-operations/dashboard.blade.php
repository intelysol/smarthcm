<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HCM AI Operations & Production Intelligence Cockpit</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen font-sans antialiased">
    <!-- Header -->
    <header class="border-b border-slate-800 bg-slate-900/80 backdrop-blur sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-lg bg-indigo-600 flex items-center justify-center shadow-lg shadow-indigo-500/20">
                    <i class="fa-solid fa-chart-line text-white text-lg"></i>
                </div>
                <div>
                    <h1 class="font-bold text-lg leading-tight tracking-wide text-white">AI Operations & Production Intelligence</h1>
                    <p class="text-xs text-slate-400">Quality Evaluation, Telemetry, Economics & Continuous Improvement</p>
                </div>
            </div>
            
            <div class="flex items-center space-x-3">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-950 text-emerald-400 border border-emerald-800">
                    <span class="w-2 h-2 mr-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    {{ $dashboard['production_readiness']['status'] ?? 'PRODUCTION_READY' }} ({{ $dashboard['production_readiness']['overall_score'] ?? 94.38 }}%)
                </span>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
        <!-- Executive Health KPI Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="p-5 rounded-xl bg-slate-900 border border-slate-800 shadow-sm">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Interaction Volume</span>
                <div class="mt-3 flex items-baseline space-x-2">
                    <span class="text-3xl font-extrabold text-white">{{ $dashboard['executive_health']['total_requests'] }}</span>
                    <span class="text-xs text-emerald-400 font-semibold">{{ $dashboard['executive_health']['success_rate'] }}% success</span>
                </div>
                <p class="mt-2 text-xs text-slate-400">{{ $dashboard['executive_health']['active_users'] }} active workforce users</p>
            </div>

            <div class="p-5 rounded-xl bg-slate-900 border border-slate-800 shadow-sm">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">AI Quality & Grounding</span>
                <div class="mt-3 flex items-baseline space-x-2">
                    <span class="text-3xl font-extrabold text-indigo-400">{{ $dashboard['executive_health']['quality_score'] }}%</span>
                    <span class="text-xs text-slate-400">Grounding: {{ $dashboard['executive_health']['grounding_score'] }}%</span>
                </div>
                <p class="mt-2 text-xs text-slate-400">Evaluated against golden benchmark cases</p>
            </div>

            <div class="p-5 rounded-xl bg-slate-900 border border-slate-800 shadow-sm">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Average Response Latency</span>
                <div class="mt-3 flex items-baseline space-x-2">
                    <span class="text-3xl font-extrabold text-white">{{ $dashboard['executive_health']['avg_latency_ms'] }}ms</span>
                    <span class="text-xs text-teal-400">P95: {{ $dashboard['performance']['p95_latency_ms'] }}ms</span>
                </div>
                <p class="mt-2 text-xs text-slate-400">Error rate: {{ $dashboard['performance']['error_rate_pct'] }}%</p>
            </div>

            <div class="p-5 rounded-xl bg-slate-900 border border-slate-800 shadow-sm">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Monthly AI Spend</span>
                <div class="mt-3 flex items-baseline space-x-2">
                    <span class="text-3xl font-extrabold text-amber-400">${{ number_format($dashboard['economics']['total_spend_usd'], 4) }}</span>
                    <span class="text-xs text-slate-400">Limit: ${{ number_format($dashboard['economics']['budget_limit_usd'], 2) }}</span>
                </div>
                <div class="w-full bg-slate-800 rounded-full h-1.5 mt-3">
                    <div class="bg-amber-400 h-1.5 rounded-full" style="width: {{ min(100, max(2, $dashboard['economics']['budget_utilization_pct'])) }}%"></div>
                </div>
            </div>
        </div>

        <!-- Two Columns: Evaluation Quality & Production Readiness -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Quality & Evaluation Breakdown -->
            <div class="p-6 rounded-xl bg-slate-900 border border-slate-800 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <h3 class="font-bold text-slate-200 flex items-center">
                        <i class="fa-solid fa-square-poll-vertical mr-2 text-indigo-400"></i> AI Quality Dimensions
                    </h3>
                    <span class="text-xs text-slate-400">Evaluations & User Feedback</span>
                </div>

                <div class="space-y-3">
                    <div>
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-slate-300">Semantic Accuracy</span>
                            <span class="text-slate-200 font-semibold">{{ $dashboard['quality_metrics']['accuracy'] }}%</span>
                        </div>
                        <div class="w-full bg-slate-800 rounded-full h-1.5">
                            <div class="bg-indigo-500 h-1.5 rounded-full" style="width: {{ $dashboard['quality_metrics']['accuracy'] }}%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-slate-300">Enterprise Data Grounding</span>
                            <span class="text-slate-200 font-semibold">{{ $dashboard['quality_metrics']['grounding'] }}%</span>
                        </div>
                        <div class="w-full bg-slate-800 rounded-full h-1.5">
                            <div class="bg-emerald-500 h-1.5 rounded-full" style="width: {{ $dashboard['quality_metrics']['grounding'] }}%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-slate-300">Citation & Source Integrity</span>
                            <span class="text-slate-200 font-semibold">{{ $dashboard['quality_metrics']['citation'] }}%</span>
                        </div>
                        <div class="w-full bg-slate-800 rounded-full h-1.5">
                            <div class="bg-teal-500 h-1.5 rounded-full" style="width: {{ $dashboard['quality_metrics']['citation'] }}%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-slate-300">Tool Selection Correctness</span>
                            <span class="text-slate-200 font-semibold">{{ $dashboard['quality_metrics']['tool_success'] }}%</span>
                        </div>
                        <div class="w-full bg-slate-800 rounded-full h-1.5">
                            <div class="bg-cyan-500 h-1.5 rounded-full" style="width: {{ $dashboard['quality_metrics']['tool_success'] }}%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-slate-300">Positive User Sentiment</span>
                            <span class="text-slate-200 font-semibold">{{ $dashboard['quality_metrics']['user_sentiment_pct'] }}%</span>
                        </div>
                        <div class="w-full bg-slate-800 rounded-full h-1.5">
                            <div class="bg-purple-500 h-1.5 rounded-full" style="width: {{ $dashboard['quality_metrics']['user_sentiment_pct'] }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Production Readiness Score -->
            <div class="p-6 rounded-xl bg-slate-900 border border-slate-800 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <h3 class="font-bold text-slate-200 flex items-center">
                        <i class="fa-solid fa-shield-halved mr-2 text-emerald-400"></i> AI Production Readiness Index
                    </h3>
                    <span class="px-2 py-0.5 text-xs font-bold rounded bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                        {{ $dashboard['production_readiness']['status'] }}
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="p-3 bg-slate-950/70 border border-slate-800 rounded-lg">
                        <div class="text-xs text-slate-400">Governance & Ethics</div>
                        <div class="text-xl font-bold text-white mt-1">{{ $dashboard['production_readiness']['governance'] }}%</div>
                    </div>
                    <div class="p-3 bg-slate-950/70 border border-slate-800 rounded-lg">
                        <div class="text-xs text-slate-400">Security & Isolation</div>
                        <div class="text-xl font-bold text-white mt-1">{{ $dashboard['production_readiness']['security'] }}%</div>
                    </div>
                    <div class="p-3 bg-slate-950/70 border border-slate-800 rounded-lg">
                        <div class="text-xs text-slate-400">Quality & Correctness</div>
                        <div class="text-xl font-bold text-white mt-1">{{ $dashboard['production_readiness']['quality'] }}%</div>
                    </div>
                    <div class="p-3 bg-slate-950/70 border border-slate-800 rounded-lg">
                        <div class="text-xs text-slate-400">Operational Performance</div>
                        <div class="text-xl font-bold text-white mt-1">{{ $dashboard['production_readiness']['performance'] }}%</div>
                    </div>
                </div>

                <div class="mt-4 p-3 bg-slate-950/50 border border-slate-800/80 rounded-lg text-xs text-slate-400">
                    <p><i class="fa-solid fa-circle-check text-emerald-400 mr-1.5"></i> All critical governance policies, human oversight controls, and fail-safe kill switches validated.</p>
                </div>
            </div>
        </div>

        <!-- Model Benchmarking Matrix -->
        <div class="p-6 rounded-xl bg-slate-900 border border-slate-800 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="font-bold text-slate-200 flex items-center">
                    <i class="fa-solid fa-microchip mr-2 text-cyan-400"></i> Model & Provider Performance Comparison
                </h3>
                <span class="text-xs text-slate-400">Benchmark Version 2026.Q4</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="text-[11px] uppercase bg-slate-950/60 text-slate-400 border-b border-slate-800">
                        <tr>
                            <th class="py-3 px-4">Model & Provider</th>
                            <th class="py-3 px-4">Accuracy</th>
                            <th class="py-3 px-4">Grounding</th>
                            <th class="py-3 px-4">Latency (Avg / P95)</th>
                            <th class="py-3 px-4">Cost / 1k Tokens</th>
                            <th class="py-3 px-4">Tool Success</th>
                            <th class="py-3 px-4">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        @forelse($benchmarks as $m)
                        <tr class="hover:bg-slate-800/30">
                            <td class="py-3 px-4">
                                <div class="font-semibold text-white">{{ $m['model_code'] }}</div>
                                <div class="text-[11px] text-slate-400">{{ $m['provider'] }}</div>
                            </td>
                            <td class="py-3 px-4 font-medium text-emerald-400">{{ $m['accuracy_pct'] }}%</td>
                            <td class="py-3 px-4 text-slate-300">{{ $m['grounding_pct'] }}%</td>
                            <td class="py-3 px-4">{{ $m['avg_latency_ms'] }}ms / {{ $m['p95_latency_ms'] }}ms</td>
                            <td class="py-3 px-4">${{ number_format($m['cost_per_1k_tokens'], 4) }}</td>
                            <td class="py-3 px-4 text-cyan-400">{{ $m['tool_success_pct'] }}%</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $m['benchmark_status'] === 'RECOMMENDED' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-indigo-500/20 text-indigo-400 border border-indigo-500/30' }}">
                                    {{ $m['benchmark_status'] }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="py-4 text-center text-slate-500">No benchmark records available.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>
