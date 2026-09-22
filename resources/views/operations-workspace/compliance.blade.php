@extends('shells.operations')

@section('title', 'Enterprise Compliance, Privacy & Governance — Operations')

@section('content')
<div class="space-y-6">

    <!-- Header Banner -->
    <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-6 shadow-md flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-mono text-[#C9A227] font-semibold uppercase tracking-wider mb-1">
                <i class="fa-solid fa-scale-balanced text-[#C9A227]"></i>
                <span>Enterprise Governance &amp; Regulatory Operations</span>
            </div>
            <h1 class="text-2xl font-black text-white tracking-tight">Compliance &amp; Privacy Control Plane</h1>
            <p class="text-xs text-zinc-400 mt-0.5">Continuous control monitoring, SHA-256 evidence verification, audit findings remediation, and privacy rights governance.</p>
        </div>
        <div class="flex items-center space-x-3">
            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold {{ $governanceScorecard['failed_tests'] > 0 ? 'bg-rose-500/10 text-rose-400 border border-rose-500/20' : 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' }}">
                <span class="w-2 h-2 rounded-full {{ $governanceScorecard['failed_tests'] > 0 ? 'bg-rose-400' : 'bg-emerald-400' }} animate-pulse mr-2"></span>
                Governance Score: {{ $governanceScorecard['compliance_score_percent'] }}%
            </span>
            <a href="{{ route('operations.data-lifecycle') }}" class="px-3.5 py-2 rounded-xl bg-zinc-800 hover:bg-zinc-700 text-zinc-200 font-semibold text-xs transition border border-zinc-700 flex items-center">
                <i class="fa-solid fa-recycle mr-1.5 text-[#C9A227]"></i> Lifecycle &amp; Retention
            </a>
        </div>
    </div>

    <!-- Governance Telemetry Scorecard -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-4">
            <span class="text-xs font-semibold text-zinc-400 uppercase tracking-wider">Monitored Frameworks</span>
            <div class="mt-2 text-2xl font-black text-white font-mono">{{ $governanceScorecard['frameworks_active'] }} <span class="text-xs text-zinc-400 font-normal">Catalog</span></div>
            <p class="text-xs text-zinc-400 mt-1">{{ $governanceScorecard['controls_active'] }} baseline controls mapped</p>
        </div>
        <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-4">
            <span class="text-xs font-semibold text-zinc-400 uppercase tracking-wider">Control Tests Passed</span>
            <div class="mt-2 text-2xl font-black text-emerald-400 font-mono">{{ $governanceScorecard['passed_tests'] }} <span class="text-xs text-zinc-400 font-normal">Verified</span></div>
            <p class="text-xs text-zinc-400 mt-1">Failed: {{ $governanceScorecard['failed_tests'] }} detected</p>
        </div>
        <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-4">
            <span class="text-xs font-semibold text-zinc-400 uppercase tracking-wider">Open Audit Findings</span>
            <div class="mt-2 text-2xl font-black {{ $governanceScorecard['open_findings'] > 0 ? 'text-rose-400' : 'text-emerald-400' }} font-mono">{{ $governanceScorecard['open_findings'] }}</div>
            <p class="text-xs text-zinc-400 mt-1">Under formal remediation</p>
        </div>
        <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-4">
            <span class="text-xs font-semibold text-zinc-400 uppercase tracking-wider">Approved Exceptions</span>
            <div class="mt-2 text-2xl font-black text-[#C9A227] font-mono">{{ $governanceScorecard['active_exceptions'] }}</div>
            <p class="text-xs text-zinc-400 mt-1">With compensating controls</p>
        </div>
    </div>

    <!-- Active Regulatory Frameworks -->
    <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-6 shadow-md">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-white tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-landmark text-[#C9A227]"></i>
                Certified Regulatory Frameworks
            </h2>
            <span class="text-xs font-mono text-zinc-400">Total: {{ $frameworks->count() }} Standard Standards</span>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @forelse($frameworks as $fw)
            <div class="p-4 rounded-xl bg-zinc-950 border border-zinc-800 space-y-2">
                <div class="flex items-center justify-between">
                    <span class="px-2 py-0.5 text-[10px] font-bold rounded bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 uppercase font-mono">{{ $fw->code }}</span>
                    <span class="text-[11px] text-zinc-400 font-mono">v{{ $fw->version }}</span>
                </div>
                <div class="text-sm font-bold text-white">{{ $fw->name }}</div>
                <div class="text-xs text-zinc-400">Authority: <span class="text-zinc-300">{{ $fw->authority }}</span></div>
                <div class="text-xs text-zinc-400">Jurisdiction: <span class="text-zinc-300">{{ $fw->jurisdiction }}</span></div>
                <div class="pt-2 border-t border-zinc-800 flex items-center justify-between text-xs">
                    <span class="text-zinc-400 font-medium">Mapped Controls:</span>
                    <span class="font-mono font-bold text-emerald-400">{{ $fw->controls_count }}</span>
                </div>
            </div>
            @empty
            <div class="col-span-3 p-6 text-center text-zinc-400 text-xs">
                No compliance frameworks initialized.
            </div>
            @endforelse
        </div>
    </div>

    <!-- Two Column Grid: Tests & Findings -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Recent Control Test Executions -->
        <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-6 shadow-md">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base font-bold text-white tracking-tight flex items-center gap-2">
                    <i class="fa-solid fa-list-check text-emerald-400"></i>
                    Recent Control Test Executions
                </h2>
                <span class="text-xs text-zinc-400">SHA-256 Verified</span>
            </div>
            <div class="space-y-3">
                @forelse($recentTests as $t)
                <div class="p-3.5 rounded-xl bg-zinc-950 border border-zinc-800 space-y-2">
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-mono font-bold text-zinc-200">{{ $t->control?->code ?? 'CTL' }}</span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $t->result === 'PASS' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border border-rose-500/20' }}">
                            {{ $t->result }}
                        </span>
                    </div>
                    <div class="text-xs text-zinc-300">{{ $t->test_procedure }}</div>
                    <div class="text-[11px] font-mono text-zinc-500 truncate" title="{{ $t->evidence_hash_sha256 }}">
                        Hash: {{ Str::limit($t->evidence_hash_sha256, 32) }}
                    </div>
                    <div class="text-[11px] text-zinc-400 flex items-center justify-between">
                        <span>Auditor: {{ $t->tested_by }}</span>
                        <span>{{ $t->tested_at?->diffForHumans() ?? 'Recently' }}</span>
                    </div>
                </div>
                @empty
                <div class="p-4 text-center text-xs text-zinc-400">
                    No control test runs recorded yet.
                </div>
                @endforelse
            </div>
        </div>

        <!-- Open Audit Findings & Remediation -->
        <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-6 shadow-md">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base font-bold text-white tracking-tight flex items-center gap-2">
                    <i class="fa-solid fa-triangle-exclamation text-rose-400"></i>
                    Audit Findings &amp; Remediation
                </h2>
                <span class="text-xs text-zinc-400 font-mono">{{ $governanceScorecard['open_findings'] }} Open</span>
            </div>
            <div class="space-y-3">
                @forelse($recentFindings as $finding)
                <div class="p-3.5 rounded-xl bg-zinc-950 border border-zinc-800 space-y-2">
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-semibold text-white">{{ $finding->title }}</span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/20">
                            {{ $finding->severity }}
                        </span>
                    </div>
                    <div class="text-xs text-zinc-400">{{ $finding->remediation_plan }}</div>
                    <div class="text-[11px] text-zinc-400 flex items-center justify-between pt-1 border-t border-zinc-800">
                        <span>Owner: <strong class="text-zinc-300">{{ $finding->owner }}</strong></span>
                        <span>Due: <strong class="text-zinc-300">{{ $finding->due_date?->format('Y-m-d') ?? 'N/A' }}</strong></span>
                    </div>
                </div>
                @empty
                <div class="p-6 text-center text-xs text-emerald-400 flex flex-col items-center">
                    <i class="fa-solid fa-circle-check text-2xl mb-2"></i>
                    No open findings. All automated controls compliant.
                </div>
                @endforelse
            </div>
        </div>

    </div>

    <!-- Privacy Management & DSAR Operations Overview -->
    <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-6 shadow-md">
        <h2 class="text-lg font-bold text-white tracking-tight flex items-center gap-2 mb-4">
            <i class="fa-solid fa-user-shield text-[#C9A227]"></i>
            Privacy Management &amp; Data Subject Rights (DSAR)
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="p-4 rounded-xl bg-zinc-950 border border-zinc-800">
                <span class="text-xs text-zinc-400 block font-semibold uppercase">Total Privacy Requests</span>
                <div class="text-2xl font-black text-white font-mono mt-1">{{ $privacyMetrics['total_requests'] }}</div>
                <p class="text-xs text-zinc-400 mt-1">{{ $privacyMetrics['pending_requests'] }} pending review</p>
            </div>
            <div class="p-4 rounded-xl bg-zinc-950 border border-zinc-800">
                <span class="text-xs text-zinc-400 block font-semibold uppercase">Fulfillment Rate</span>
                <div class="text-2xl font-black text-emerald-400 font-mono mt-1">{{ $privacyMetrics['fulfillment_rate_percent'] }}%</div>
                <p class="text-xs text-zinc-400 mt-1">{{ $privacyMetrics['completed_requests'] }} completed exports</p>
            </div>
            <div class="p-4 rounded-xl bg-zinc-950 border border-zinc-800">
                <span class="text-xs text-zinc-400 block font-semibold uppercase">Retention Interceptions</span>
                <div class="text-2xl font-black text-[#C9A227] font-mono mt-1">{{ $privacyMetrics['rejected_requests'] }}</div>
                <p class="text-xs text-zinc-400 mt-1">Blocked by legal hold / statutory floor</p>
            </div>
            <div class="p-4 rounded-xl bg-zinc-950 border border-zinc-800">
                <span class="text-xs text-zinc-400 block font-semibold uppercase">DPIAs &amp; High Risk</span>
                <div class="text-2xl font-black text-indigo-400 font-mono mt-1">{{ $privacyMetrics['dpias_count'] }}</div>
                <p class="text-xs text-zinc-400 mt-1">{{ $privacyMetrics['dpias_high_risk'] }} flagged high risk</p>
            </div>
        </div>
    </div>

</div>
@endsection
