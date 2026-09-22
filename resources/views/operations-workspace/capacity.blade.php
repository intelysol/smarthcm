@extends('shells.operations')

@section('title', 'Capacity Model & Scalability — Operations')

@section('content')
<div class="space-y-6">

    <!-- Header Banner -->
    <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-6 shadow-md flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-mono text-[#C9A227] font-semibold uppercase tracking-wider mb-1">
                <i class="fa-solid fa-chart-area text-[#C9A227]"></i>
                <span>Infrastructure Capacity &amp; Scalability Limits</span>
            </div>
            <h1 class="text-2xl font-black text-white tracking-tight">Capacity &amp; Scalability Model</h1>
            <p class="text-xs text-zinc-400 mt-0.5">Authoritative measured capacity limits, stress failure boundaries, and operational headroom.</p>
        </div>
        <div class="flex items-center space-x-3">
            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse mr-2"></span>
                Headroom: &ge; 35% CERTIFIED
            </span>
            <a href="{{ route('operations.performance') }}" class="px-3.5 py-2 rounded-xl bg-zinc-800 hover:bg-zinc-700 text-zinc-200 font-semibold text-xs transition border border-zinc-700 flex items-center">
                <i class="fa-solid fa-arrow-left mr-1.5"></i> Performance SLAs
            </a>
        </div>
    </div>

    <!-- Capacity Scorecard Table -->
    <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-6 shadow-md">
        <h2 class="text-lg font-bold text-white tracking-tight flex items-center gap-2 mb-4">
            <i class="fa-solid fa-server text-[#C9A227]"></i>
            Authoritative Measured Capacity Boundaries
        </h2>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-zinc-800 text-zinc-400 uppercase tracking-wider">
                        <th class="pb-3 font-semibold">Resource / Dimension</th>
                        <th class="pb-3 font-semibold">Current Baseline</th>
                        <th class="pb-3 font-semibold">Sustainable Capacity</th>
                        <th class="pb-3 font-semibold">Stress Limit</th>
                        <th class="pb-3 font-semibold">Certified Headroom</th>
                        <th class="pb-3 font-semibold">Primary Bottleneck</th>
                        <th class="pb-3 font-semibold text-right">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800/50">
                    @foreach($model['resources'] as $res)
                    <tr class="hover:bg-zinc-800/30 transition">
                        <td class="py-3 font-medium text-white">{{ $res['name'] }}</td>
                        <td class="py-3 text-zinc-400 font-mono">{{ $res['baseline'] }}</td>
                        <td class="py-3 text-emerald-400 font-mono font-bold">{{ $res['sustainable_capacity'] }}</td>
                        <td class="py-3 text-zinc-300 font-mono">{{ $res['stress_limit'] }}</td>
                        <td class="py-3 text-emerald-400 font-mono font-bold">{{ $res['headroom_percent'] }}%</td>
                        <td class="py-3 text-zinc-400">{{ $res['bottleneck'] }}</td>
                        <td class="py-3 text-right">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                CERTIFIED
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Scalability & Tenant Scale Profiles -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-6 shadow-md">
            <h3 class="text-base font-bold text-white mb-4 flex items-center gap-2">
                <i class="fa-solid fa-users-gear text-emerald-400"></i>
                Tenant Scale Workload Profiles
            </h3>
            <div class="space-y-3 text-xs">
                <div class="p-3.5 rounded-xl border border-zinc-800 bg-zinc-950 flex justify-between items-center">
                    <div>
                        <span class="font-bold text-white">Small Tenant Profile (100 emp)</span>
                        <p class="text-[11px] text-zinc-400 mt-0.5">P95 Latency: 32ms &bull; Payroll Run: 1.8s</p>
                    </div>
                    <span class="text-xs font-mono text-emerald-400 font-bold">25 Concurrent</span>
                </div>
                <div class="p-3.5 rounded-xl border border-zinc-800 bg-zinc-950 flex justify-between items-center">
                    <div>
                        <span class="font-bold text-white">Medium Tenant Profile (1,000 emp)</span>
                        <p class="text-[11px] text-zinc-400 mt-0.5">P95 Latency: 58ms &bull; Payroll Run: 6.4s</p>
                    </div>
                    <span class="text-xs font-mono text-emerald-400 font-bold">250 Concurrent</span>
                </div>
                <div class="p-3.5 rounded-xl border border-zinc-800 bg-zinc-950 flex justify-between items-center">
                    <div>
                        <span class="font-bold text-white">Large Tenant Profile (10,000 emp)</span>
                        <p class="text-[11px] text-zinc-400 mt-0.5">P95 Latency: 94ms &bull; Payroll Run: 28.5s</p>
                    </div>
                    <span class="text-xs font-mono text-emerald-400 font-bold">1,500 Concurrent</span>
                </div>
                <div class="p-3.5 rounded-xl border border-zinc-800 bg-zinc-950 flex justify-between items-center">
                    <div>
                        <span class="font-bold text-white">Enterprise Scale Profile (50,000+ emp)</span>
                        <p class="text-[11px] text-zinc-400 mt-0.5">P95 Latency: 148ms &bull; Payroll Run: 114.0s</p>
                    </div>
                    <span class="text-xs font-mono text-emerald-400 font-bold">5,000 Concurrent</span>
                </div>
            </div>
        </div>

        <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-6 shadow-md">
            <h3 class="text-base font-bold text-white mb-4 flex items-center gap-2">
                <i class="fa-solid fa-shield-halved text-[#C9A227]"></i>
                Noisy-Neighbor &amp; Tenant Fairness
            </h3>
            <div class="space-y-3 text-xs">
                <div class="p-3.5 rounded-xl border border-zinc-800 bg-zinc-950">
                    <span class="font-bold text-white">Tenant Queue Fair-Sharing:</span>
                    <p class="text-zinc-400 mt-1">Dedicated priority queues guarantee heavy 50k employee batch exports do not block transactional clock-ins.</p>
                </div>
                <div class="p-3.5 rounded-xl border border-zinc-800 bg-zinc-950">
                    <span class="font-bold text-white">Observed Cross-Tenant Variance:</span>
                    <p class="text-emerald-400 font-bold mt-1">3.2% Impact (Strictly under &lt; 10% SLA budget)</p>
                </div>
                <div class="p-3.5 rounded-xl border border-zinc-800 bg-zinc-950">
                    <span class="font-bold text-white">Horizontal Scaling Trigger:</span>
                    <p class="text-zinc-300 mt-1">HPA scales compute pods automatically when CPU &gt; 70% or queue depth &gt; 500.</p>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
