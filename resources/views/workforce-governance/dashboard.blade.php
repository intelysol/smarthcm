<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HCM Workforce Data Governance & Quality Center</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen font-sans antialiased">
    <!-- Header -->
    <header class="border-b border-slate-800 bg-slate-900/80 backdrop-blur sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-lg bg-teal-600 flex items-center justify-center shadow-lg shadow-teal-500/30">
                    <i class="fa-solid fa-shield-halved text-white text-lg"></i>
                </div>
                <div>
                    <h1 class="font-bold text-lg leading-tight tracking-wide text-white">Workforce Data Governance Center</h1>
                    <p class="text-xs text-slate-400">Master Data Management, People Data Quality & Lineage Cockpit</p>
                </div>
            </div>
            
            <div class="flex items-center space-x-4">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-950 text-emerald-400 border border-emerald-800">
                    <span class="w-1.5 h-1.5 mr-1.5 rounded-full bg-emerald-400"></span>
                    Governance Active
                </span>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
        <!-- Top Metrics Row -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="p-5 rounded-xl bg-slate-900 border border-slate-800 shadow-sm">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Overall Data Quality</span>
                <div class="mt-3 flex items-baseline space-x-2">
                    <span class="text-3xl font-extrabold text-emerald-400">{{ $dashboard['overall_quality_score'] }}%</span>
                    <span class="text-xs text-slate-400">Benchmark 95.0%</span>
                </div>
                <p class="mt-2 text-xs text-slate-400">Evaluated across 8 core dimensions</p>
            </div>

            <div class="p-5 rounded-xl bg-slate-900 border border-slate-800 shadow-sm">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Governed Data Assets</span>
                <div class="mt-3 flex items-baseline space-x-2">
                    <span class="text-3xl font-extrabold text-white">{{ $dashboard['total_governed_assets'] }}</span>
                    <span class="text-xs text-teal-400 font-medium">Catalogued</span>
                </div>
                <p class="mt-2 text-xs text-slate-400">Core HR, Payroll, Talent, Scheduling, Cost</p>
            </div>

            <div class="p-5 rounded-xl bg-slate-900 border border-slate-800 shadow-sm">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Open Quality Issues</span>
                <div class="mt-3 flex items-baseline space-x-2">
                    <span class="text-3xl font-extrabold text-white">{{ $dashboard['open_quality_issues'] }}</span>
                    <span class="text-xs text-amber-400 font-medium">Pending Review</span>
                </div>
                <p class="mt-2 text-xs text-slate-400">{{ $dashboard['critical_quality_issues'] }} Critical Severity</p>
            </div>

            <div class="p-5 rounded-xl bg-slate-900 border border-slate-800 shadow-sm">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Reconciliation Status</span>
                <div class="mt-3 flex items-baseline space-x-2">
                    <span class="text-2xl font-bold text-emerald-400">Balanced</span>
                    <span class="text-xs text-slate-400">Core vs Payroll</span>
                </div>
                <p class="mt-2 text-xs text-slate-400">Discrepancy tolerance: 0 variance</p>
            </div>
        </div>

        <!-- Middle Section: Dimension Scores & Catalog -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Quality Dimensions Breakdown -->
            <div class="p-6 rounded-xl bg-slate-900 border border-slate-800 lg:col-span-1 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <h3 class="font-bold text-slate-200 flex items-center">
                        <i class="fa-solid fa-list-check mr-2 text-teal-400"></i> Quality Dimensions
                    </h3>
                    <span class="text-xs text-slate-400">Scorecard</span>
                </div>

                <div class="space-y-3">
                    @foreach($dashboard['dimension_scores'] as $dimension => $score)
                    <div>
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-slate-300">{{ $dimension }}</span>
                            <span class="font-semibold {{ $score >= 95 ? 'text-emerald-400' : 'text-amber-400' }}">{{ $score }}%</span>
                        </div>
                        <div class="w-full bg-slate-800 rounded-full h-1.5">
                            <div class="{{ $score >= 95 ? 'bg-emerald-500' : 'bg-amber-500' }} h-1.5 rounded-full" style="width: {{ $score }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Governed Assets Table -->
            <div class="p-6 rounded-xl bg-slate-900 border border-slate-800 lg:col-span-2 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <h3 class="font-bold text-slate-200 flex items-center">
                        <i class="fa-solid fa-database mr-2 text-teal-400"></i> Governed Asset Catalog
                    </h3>
                    <span class="text-xs text-slate-400">{{ count($assets) }} Registered</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-300">
                        <thead class="bg-slate-950 text-slate-400 uppercase text-[10px] tracking-wider">
                            <tr>
                                <th class="p-3">Asset Code</th>
                                <th class="p-3">Name</th>
                                <th class="p-3">Domain</th>
                                <th class="p-3">System of Record</th>
                                <th class="p-3">Owner</th>
                                <th class="p-3">Quality</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800">
                            @forelse($assets as $a)
                            <tr class="hover:bg-slate-950/40">
                                <td class="p-3 font-mono text-teal-400">{{ $a['asset_code'] }}</td>
                                <td class="p-3 font-semibold text-white">{{ $a['name'] }}</td>
                                <td class="p-3"><span class="px-2 py-0.5 rounded bg-slate-800 text-slate-300">{{ $a['domain'] }}</span></td>
                                <td class="p-3">{{ $a['system_of_record'] }}</td>
                                <td class="p-3 text-slate-400">{{ $a['business_owner'] }}</td>
                                <td class="p-3 font-bold text-emerald-400">{{ $a['current_quality_score'] }}%</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="p-4 text-center text-slate-500 italic">No governed assets catalogued yet.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
