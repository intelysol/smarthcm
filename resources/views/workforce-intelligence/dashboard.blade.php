<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HCM Workforce Intelligence Command Center</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen font-sans antialiased">
    <!-- Header -->
    <header class="border-b border-slate-800 bg-slate-900/80 backdrop-blur sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-lg bg-indigo-600 flex items-center justify-center shadow-lg shadow-indigo-500/30">
                    <i class="fa-solid fa-satellite-dish text-white text-lg"></i>
                </div>
                <div>
                    <h1 class="font-bold text-lg leading-tight tracking-wide text-white">Workforce Intelligence Command Center</h1>
                    <p class="text-xs text-slate-400">Enterprise Cross-Domain Decision & People Analytics Cockpit</p>
                </div>
            </div>
            
            <div class="flex items-center space-x-4">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-950 text-emerald-400 border border-emerald-800">
                    <span class="w-1.5 h-1.5 mr-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    Live Operational Pulse
                </span>
                <div class="text-xs text-slate-400 bg-slate-800/80 px-3 py-1.5 rounded-md border border-slate-700">
                    Persona: <span class="text-indigo-400 font-semibold uppercase">{{ $persona }}</span>
                </div>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
        <!-- Top Executive Scorecard Strip -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Health Score Card -->
            <div class="p-5 rounded-xl bg-slate-900 border border-slate-800 shadow-sm relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Composite Health</span>
                    <span class="px-2 py-0.5 text-xs rounded font-medium bg-indigo-500/20 text-indigo-300">{{ $healthIndex->healthBand }}</span>
                </div>
                <div class="mt-3 flex items-baseline space-x-2">
                    <span class="text-3xl font-extrabold text-white">{{ $healthIndex->compositeScore }}</span>
                    <span class="text-xs text-slate-400">/ 100</span>
                </div>
                <div class="mt-3 w-full bg-slate-800 rounded-full h-1.5">
                    <div class="bg-indigo-500 h-1.5 rounded-full" style="width: {{ $healthIndex->compositeScore }}%"></div>
                </div>
            </div>

            <!-- Total Headcount -->
            <div class="p-5 rounded-xl bg-slate-900 border border-slate-800 shadow-sm">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Active Headcount</span>
                <div class="mt-3 flex items-baseline space-x-2">
                    <span class="text-3xl font-extrabold text-white">{{ number_format($scorecard->totalHeadcount) }}</span>
                    <span class="text-xs text-emerald-400 font-medium"><i class="fa-solid fa-arrow-up"></i> Stable</span>
                </div>
                <p class="mt-2 text-xs text-slate-400">{{ $scorecard->totalFte }} Scheduled FTE</p>
            </div>

            <!-- Workforce Cost -->
            <div class="p-5 rounded-xl bg-slate-900 border border-slate-800 shadow-sm">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Workforce Cost (Cycle)</span>
                <div class="mt-3 flex items-baseline space-x-2">
                    <span class="text-3xl font-extrabold text-white">${{ number_format($scorecard->totalWorkforceCost, 0) }}</span>
                    <span class="text-xs text-slate-400">Avg ${{ number_format($scorecard->averageCostPerFte, 0) }}/FTE</span>
                </div>
                <p class="mt-2 text-xs text-slate-400">Cost & Overtime within budget boundary</p>
            </div>

            <!-- Productivity Index -->
            <div class="p-5 rounded-xl bg-slate-900 border border-slate-800 shadow-sm">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Productivity Score</span>
                <div class="mt-3 flex items-baseline space-x-2">
                    <span class="text-3xl font-extrabold text-emerald-400">{{ $scorecard->overallProductivityScore }}%</span>
                    <span class="text-xs text-slate-400">Baseline 85.0%</span>
                </div>
                <p class="mt-2 text-xs text-slate-400">Absence Rate: {{ $scorecard->absenceRate }}% | Voluntary Churn: {{ $scorecard->turnoverRate }}%</p>
            </div>
        </div>

        <!-- Cross-Domain Cockpit Middle Tier: Health Breakdown & Operational Pulse -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Health Dimensions Breakdown -->
            <div class="p-6 rounded-xl bg-slate-900 border border-slate-800 lg:col-span-1 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <h3 class="font-bold text-slate-200 flex items-center">
                        <i class="fa-solid fa-chart-pie mr-2 text-indigo-400"></i> Health Dimensions
                    </h3>
                    <span class="text-xs text-slate-400">Weighted 0-100</span>
                </div>

                <div class="space-y-3">
                    <div>
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-slate-300">Productivity (25%)</span>
                            <span class="font-semibold text-emerald-400">{{ $healthIndex->productivityDimensionScore }}/100</span>
                        </div>
                        <div class="w-full bg-slate-800 rounded-full h-1.5">
                            <div class="bg-emerald-500 h-1.5 rounded-full" style="width: {{ $healthIndex->productivityDimensionScore }}%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-slate-300">Capacity & Staffing (20%)</span>
                            <span class="font-semibold text-indigo-400">{{ $healthIndex->capacityDimensionScore }}/100</span>
                        </div>
                        <div class="w-full bg-slate-800 rounded-full h-1.5">
                            <div class="bg-indigo-500 h-1.5 rounded-full" style="width: {{ $healthIndex->capacityDimensionScore }}%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-slate-300">Cost & Budget Adherence (20%)</span>
                            <span class="font-semibold text-amber-400">{{ $healthIndex->costDimensionScore }}/100</span>
                        </div>
                        <div class="w-full bg-slate-800 rounded-full h-1.5">
                            <div class="bg-amber-500 h-1.5 rounded-full" style="width: {{ $healthIndex->costDimensionScore }}%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-slate-300">Retention & Stability (15%)</span>
                            <span class="font-semibold text-emerald-400">{{ $healthIndex->retentionDimensionScore }}/100</span>
                        </div>
                        <div class="w-full bg-slate-800 rounded-full h-1.5">
                            <div class="bg-emerald-500 h-1.5 rounded-full" style="width: {{ $healthIndex->retentionDimensionScore }}%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-slate-300">Skills & Capability Readiness (10%)</span>
                            <span class="font-semibold text-indigo-400">{{ $healthIndex->skillsDimensionScore }}/100</span>
                        </div>
                        <div class="w-full bg-slate-800 rounded-full h-1.5">
                            <div class="bg-indigo-500 h-1.5 rounded-full" style="width: {{ $healthIndex->skillsDimensionScore }}%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-slate-300">Operational Compliance (10%)</span>
                            <span class="font-semibold text-emerald-400">{{ $healthIndex->complianceDimensionScore }}/100</span>
                        </div>
                        <div class="w-full bg-slate-800 rounded-full h-1.5">
                            <div class="bg-emerald-500 h-1.5 rounded-full" style="width: {{ $healthIndex->complianceDimensionScore }}%"></div>
                        </div>
                    </div>
                </div>

                <div class="p-3 bg-slate-950/60 rounded-lg border border-slate-800 text-xs text-slate-300 mt-4 leading-relaxed">
                    <span class="font-semibold text-indigo-400">Formula Diagnosis:</span> {{ $healthIndex->summaryDiagnosis }}
                </div>
            </div>

            <!-- Real-Time Pulse & Operational Hotspots -->
            <div class="p-6 rounded-xl bg-slate-900 border border-slate-800 lg:col-span-2 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <h3 class="font-bold text-slate-200 flex items-center">
                        <i class="fa-solid fa-heart-pulse mr-2 text-rose-500"></i> Operational Pulse & Live Hotspots
                    </h3>
                    <span class="text-xs text-slate-400">Freshness: {{ $pulse->dataFreshnessTimestamp }}</span>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div class="p-3 bg-slate-950/40 rounded-lg border border-slate-800 text-center">
                        <div class="text-xs text-slate-400">Actual Hours Today</div>
                        <div class="text-lg font-bold text-slate-100 mt-1">{{ number_format($pulse->actualHoursWorkedToday, 1) }}h</div>
                    </div>
                    <div class="p-3 bg-slate-950/40 rounded-lg border border-slate-800 text-center">
                        <div class="text-xs text-slate-400">Overtime Hours Today</div>
                        <div class="text-lg font-bold text-amber-400 mt-1">{{ number_format($pulse->overtimeHoursToday, 1) }}h</div>
                    </div>
                    <div class="p-3 bg-slate-950/40 rounded-lg border border-slate-800 text-center">
                        <div class="text-xs text-slate-400">Active Critical Alerts</div>
                        <div class="text-lg font-bold text-rose-500 mt-1">{{ $pulse->openCriticalAlerts }}</div>
                    </div>
                </div>

                <div class="space-y-3 pt-2">
                    <div class="text-xs font-semibold uppercase text-slate-400">Identified Hotspots & Variances</div>
                    @foreach($pulse->hotspots as $spot)
                    <div class="p-3 rounded-lg bg-slate-950/80 border border-slate-800 flex items-center justify-between">
                        <div>
                            <div class="font-semibold text-sm text-slate-200">{{ $spot['area'] }}</div>
                            <div class="text-xs text-slate-400 mt-0.5">{{ $spot['issue'] }}</div>
                        </div>
                        <span class="px-2 py-0.5 text-xs font-semibold rounded bg-amber-500/20 text-amber-400 border border-amber-500/30">
                            {{ $spot['severity'] }}
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Lower Section: Consolidated Risk Center & Decision Queue -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Consolidated Risk Center -->
            <div class="p-6 rounded-xl bg-slate-900 border border-slate-800 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <h3 class="font-bold text-slate-200 flex items-center">
                        <i class="fa-solid fa-triangle-exclamation mr-2 text-amber-500"></i> Consolidated Risk Center
                    </h3>
                    <span class="text-xs text-slate-400">{{ count($risks) }} Identified Risks</span>
                </div>

                <div class="space-y-3">
                    @forelse($risks as $r)
                    <div class="p-3 rounded-lg bg-slate-950/60 border border-slate-800 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-300">[{{ $r['category'] }}] {{ $r['title'] }}</span>
                            <span class="px-2 py-0.5 text-xs rounded font-semibold {{ $r['severity'] === 'CRITICAL' ? 'bg-rose-500/20 text-rose-400 border border-rose-500/30' : 'bg-amber-500/20 text-amber-400 border border-amber-500/30' }}">
                                {{ $r['severity'] }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-400">{{ $r['description'] }}</p>
                        @if(!empty($r['recommended_mitigation']))
                        <div class="text-xs text-indigo-300 bg-indigo-950/30 p-2 rounded border border-indigo-900/50">
                            <strong>Mitigation:</strong> {{ $r['recommended_mitigation'] }}
                        </div>
                        @endif
                    </div>
                    @empty
                    <p class="text-xs text-slate-500 italic">No open workforce risks recorded.</p>
                    @endforelse
                </div>
            </div>

            <!-- Central Decision & Authorization Queue -->
            <div class="p-6 rounded-xl bg-slate-900 border border-slate-800 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <h3 class="font-bold text-slate-200 flex items-center">
                        <i class="fa-solid fa-list-check mr-2 text-indigo-400"></i> Action & Decision Queue
                    </h3>
                    <span class="text-xs text-slate-400">{{ count($decisions) }} Pending Actions</span>
                </div>

                <div class="space-y-3">
                    @forelse($decisions as $d)
                    <div class="p-3 rounded-lg bg-slate-950/60 border border-slate-800 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-300">{{ $d['title'] }}</span>
                            <span class="px-2 py-0.5 text-xs rounded font-semibold bg-indigo-500/20 text-indigo-300">
                                {{ $d['urgency'] }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-400">{{ $d['summary'] }}</p>
                        <div class="flex items-center justify-between pt-1">
                            <span class="text-xs text-slate-500">Domain: {{ $d['source_domain'] }}</span>
                            <div class="space-x-2">
                                <button class="px-2.5 py-1 text-xs font-semibold rounded bg-emerald-600 hover:bg-emerald-500 text-white">Approve</button>
                                <button class="px-2.5 py-1 text-xs font-semibold rounded bg-slate-800 hover:bg-slate-700 text-slate-300">Reject</button>
                            </div>
                        </div>
                    </div>
                    @empty
                    <p class="text-xs text-slate-500 italic">No pending workforce actions awaiting authorization.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </main>
</body>
</html>
