@extends('shells.operations')

@section('title', 'Data Lifecycle Management & Retention — Operations')

@section('content')
<div class="space-y-6">

    <!-- Header Banner -->
    <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-6 shadow-md flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-mono text-[#C9A227] font-semibold uppercase tracking-wider mb-1">
                <i class="fa-solid fa-recycle text-[#C9A227]"></i>
                <span>Enterprise Data Lifecycle &amp; Storage Optimization</span>
            </div>
            <h1 class="text-2xl font-black text-white tracking-tight">Data Lifecycle Management</h1>
            <p class="text-xs text-zinc-400 mt-0.5">Automated retention progression, cryptographic archival vaults, zero-destructive dry runs, and active legal holds.</p>
        </div>
        <div class="flex items-center space-x-3">
            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse mr-2"></span>
                Retention: 100% COMPLIANT
            </span>
            <a href="{{ route('operations.capacity') }}" class="px-3.5 py-2 rounded-xl bg-zinc-800 hover:bg-zinc-700 text-zinc-200 font-semibold text-xs transition border border-zinc-700 flex items-center">
                <i class="fa-solid fa-chart-area mr-1.5 text-[#C9A227]"></i> Capacity Model
            </a>
        </div>
    </div>

    <!-- Storage Telemetry Scorecard -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-4">
            <span class="text-xs font-semibold text-zinc-400 uppercase tracking-wider">Total Storage Footprint</span>
            <div class="mt-2 text-2xl font-black text-white font-mono">{{ number_format($telemetry['total_storage_mb'], 1) }} <span class="text-xs text-zinc-400 font-normal">MB</span></div>
            <p class="text-xs text-zinc-400 mt-1">Operational: {{ number_format($telemetry['active_operational_storage_mb'], 1) }} MB</p>
        </div>
        <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-4">
            <span class="text-xs font-semibold text-zinc-400 uppercase tracking-wider">Cold Archive Vault</span>
            <div class="mt-2 text-2xl font-black text-emerald-400 font-mono">{{ number_format($telemetry['cold_archive_storage_mb'], 1) }} <span class="text-xs text-zinc-400 font-normal">MB</span></div>
            <p class="text-xs text-zinc-400 mt-1">Packages: {{ $telemetry['total_archives_packages'] }} archived</p>
        </div>
        <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-4">
            <span class="text-xs font-semibold text-zinc-400 uppercase tracking-wider">Archive Eligible Records</span>
            <div class="mt-2 text-2xl font-black text-[#C9A227] font-mono">{{ number_format($telemetry['archive_eligible_records']) }}</div>
            <p class="text-xs text-zinc-400 mt-1">Ready for asynchronous extraction</p>
        </div>
        <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-4">
            <span class="text-xs font-semibold text-zinc-400 uppercase tracking-wider">Active Legal Holds</span>
            <div class="mt-2 text-2xl font-black {{ $telemetry['active_legal_holds'] > 0 ? 'text-amber-400' : 'text-emerald-400' }} font-mono">{{ $telemetry['active_legal_holds'] }}</div>
            <p class="text-xs text-zinc-400 mt-1">Purge &amp; deletion strictly blocked</p>
        </div>
    </div>

    <!-- Retention Lifecycle Progression Pipeline -->
    <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-6 shadow-md">
        <h2 class="text-lg font-bold text-white tracking-tight flex items-center gap-2 mb-4">
            <i class="fa-solid fa-arrows-split-up-and-left text-[#C9A227]"></i>
            Governed Lifecycle Progression Pipeline
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-5 gap-3 text-xs">
            <div class="p-3 rounded-xl bg-zinc-950 border border-zinc-800">
                <span class="font-bold text-emerald-400 block mb-1">1. ACTIVE</span>
                <p class="text-zinc-400 text-[11px]">Daily operational transactions. SLA &lt; 100ms.</p>
            </div>
            <div class="p-3 rounded-xl bg-zinc-950 border border-zinc-800">
                <span class="font-bold text-blue-400 block mb-1">2. AGING</span>
                <p class="text-zinc-400 text-[11px]">Completed workflow cooldown. Warm storage.</p>
            </div>
            <div class="p-3 rounded-xl bg-zinc-950 border border-zinc-800">
                <span class="font-bold text-[#C9A227] block mb-1">3. ARCHIVED</span>
                <p class="text-zinc-400 text-[11px]">Cryptographic SHA-256 package in cold vault.</p>
            </div>
            <div class="p-3 rounded-xl bg-zinc-950 border border-zinc-800">
                <span class="font-bold text-amber-400 block mb-1">4. LEGAL HOLD</span>
                <p class="text-zinc-400 text-[11px]">Litigation lock. All purge blocked.</p>
            </div>
            <div class="p-3 rounded-xl bg-zinc-950 border border-zinc-800">
                <span class="font-bold text-rose-400 block mb-1">5. SECURE DELETION</span>
                <p class="text-zinc-400 text-[11px]">Dual-approval purge with immutable certificate.</p>
            </div>
        </div>
    </div>

    <!-- Deletion Dry Run & Preview Simulator Card -->
    <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-6 shadow-md">
        <h2 class="text-lg font-bold text-white tracking-tight flex items-center gap-2 mb-4">
            <i class="fa-solid fa-shield-halved text-emerald-400"></i>
            Non-Destructive Deletion Dry-Run &amp; Dependency Preview
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
            <div class="p-3.5 rounded-xl border border-zinc-800 bg-zinc-950">
                <span class="text-zinc-400">Candidate Records:</span>
                <div class="text-xl font-bold font-mono text-white mt-1">{{ $dryRun['total_candidates'] }}</div>
            </div>
            <div class="p-3.5 rounded-xl border border-zinc-800 bg-zinc-950">
                <span class="text-zinc-400">Eligible for Purge:</span>
                <div class="text-xl font-bold font-mono text-emerald-400 mt-1">{{ $dryRun['eligible_for_deletion'] }}</div>
            </div>
            <div class="p-3.5 rounded-xl border border-zinc-800 bg-zinc-950">
                <span class="text-zinc-400">Protected by Relational Guard:</span>
                <div class="text-xl font-bold font-mono text-amber-400 mt-1">{{ $dryRun['protected_dependencies'] }}</div>
            </div>
            <div class="p-3.5 rounded-xl border border-zinc-800 bg-zinc-950">
                <span class="text-zinc-400">DB Mutations Executed:</span>
                <div class="text-xl font-bold font-mono text-emerald-400 mt-1">0 (Zero-Destructive)</div>
            </div>
        </div>
    </div>

</div>
@endsection
