@extends('shells.executive')

@section('title', 'Executive Workforce Intelligence')

@section('content')
<div class="space-y-6">

    <!-- Executive Hero Banner -->
    <div class="bg-gradient-to-r from-slate-900 to-indigo-950 border border-indigo-900/60 rounded-2xl p-6 shadow-xl flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-mono text-[#C9A227] font-semibold uppercase tracking-wider mb-1">
                <i class="fa-solid fa-chart-line text-[#C9A227]"></i>
                <span>Executive Decision Support &bull; Governed Intelligence</span>
            </div>
            <h1 class="text-2xl font-black text-white tracking-tight">Workforce Intelligence &amp; Strategy</h1>
            <p class="text-xs text-slate-400 mt-0.5">High-level insights into organization capacity, workforce ROI, headcount trajectory, and talent retention.</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ Route::has('workforce-planning.index') ? route('workforce-planning.index') : url('/workforce-planning') }}" class="px-3.5 py-2 rounded-xl bg-[#1E3A5F] hover:bg-[#142A44] border border-[#C9A227]/40 text-[#F4E7B2] font-bold text-xs shadow transition flex items-center">
                <i class="fa-solid fa-compass-drafting mr-1.5 text-[#C9A227]"></i> Scenario Modeling
            </a>
        </div>
    </div>

    <!-- Executive KPI Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-4 shadow">
            <span class="text-xs font-medium text-slate-400">Total Workforce Size</span>
            <div class="mt-2 text-2xl font-black text-white">{{ number_format($kpis['total_headcount']) }}</div>
            <p class="text-xs text-emerald-400 mt-1 font-medium">{{ $headcountSummary['active_headcount'] ?? 0 }} Active &bull; SSoR Tracked</p>
        </div>
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-4 shadow">
            <span class="text-xs font-medium text-slate-400">Retention Rate</span>
            <div class="mt-2 text-2xl font-black text-emerald-400">{{ $kpis['retention_rate'] }}</div>
            <p class="text-xs text-slate-400 mt-1">Annual turnover: {{ $kpis['annual_turnover'] }}</p>
        </div>
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-4 shadow">
            <span class="text-xs font-medium text-slate-400">Monthly Payroll Runrate</span>
            <div class="mt-2 text-2xl font-black text-white">{{ $kpis['payroll_cost_runrate'] }}</div>
            <p class="text-xs text-slate-400 mt-1">Direct compensation &amp; benefits</p>
        </div>
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-4 shadow">
            <span class="text-xs font-medium text-slate-400">Productivity Score</span>
            <div class="mt-2 text-2xl font-black text-[#C9A227]">{{ $kpis['workforce_productivity_score'] }}</div>
            <p class="text-xs text-emerald-400 mt-1">Capacity: {{ $kpis['capacity_utilization'] }}</p>
        </div>
    </div>

    <!-- Strategic Panels -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 shadow">
            <h2 class="text-sm font-bold text-white mb-1">Workforce Capacity &amp; Utilization</h2>
            <p class="text-xs text-slate-400 mb-4">Departmental distribution and headcount allocation across core product lines.</p>
            <div class="space-y-3 text-xs">
                @if(!empty($headcountSummary['by_department']) && ($kpis['total_headcount'] ?? 0) > 0)
                    @php
                        $colors = ['bg-indigo-500', 'bg-[#C9A227]', 'bg-emerald-500', 'bg-blue-500', 'bg-purple-500', 'bg-amber-500'];
                        $idx = 0;
                        $total = max(1, $kpis['total_headcount']);
                    @endphp
                    @foreach($headcountSummary['by_department'] as $deptName => $count)
                        @php
                            $pct = round(($count / $total) * 100, 1);
                            $color = $colors[$idx % count($colors)];
                            $idx++;
                        @endphp
                        <div>
                            <div class="flex justify-between text-slate-300 font-semibold mb-1">
                                <span>{{ $deptName }} ({{ $count }})</span>
                                <span class="text-white">{{ $pct }}%</span>
                            </div>
                            <div class="w-full h-2 bg-slate-800 rounded-full overflow-hidden">
                                <div class="{{ $color }} h-full rounded-full" style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="p-4 rounded-lg bg-slate-950/40 border border-slate-800 text-center text-slate-400">
                        <i class="fa-solid fa-chart-pie mb-1 text-slate-500 block text-lg"></i>
                        <span>No departmental workforce data recorded yet.</span>
                    </div>
                @endif
            </div>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 shadow">
            <h2 class="text-sm font-bold text-white mb-1">Strategic Objectives &amp; Talent Risk</h2>
            <p class="text-xs text-slate-400 mb-4">Organizational readiness and key executive succession planning.</p>
            <div class="space-y-2.5 text-xs">
                <div class="p-3 bg-slate-950/60 rounded-lg border border-slate-800 flex items-center justify-between">
                    <div>
                        <span class="font-semibold text-white block">Key Role Succession Coverage</span>
                        <span class="text-[11px] text-slate-400">Ready successor identified for 82% of critical positions</span>
                    </div>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">HEALTHY</span>
                </div>
                <div class="p-3 bg-slate-950/60 rounded-lg border border-slate-800 flex items-center justify-between">
                    <div>
                        <span class="font-semibold text-white block">Cost Center Variance</span>
                        <span class="text-[11px] text-slate-400">Workforce spend within 1.8% of budgeted Q3 allocation</span>
                    </div>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-500/20 text-indigo-400 border border-indigo-500/30">ALIGNED</span>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
