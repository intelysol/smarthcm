@extends('shells.operations')

@section('title', 'Backup & Point-in-Time Recovery — Operations')

@section('content')
<div class="space-y-6">

    <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-6 shadow-md flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-mono text-[#C9A227] font-semibold uppercase tracking-wider mb-1">
                <i class="fa-solid fa-database text-[#C9A227]"></i>
                <span>Disaster Recovery &amp; Point-in-Time Recovery</span>
            </div>
            <h1 class="text-2xl font-black text-white tracking-tight">Database Backups &amp; PITR</h1>
            <p class="text-xs text-zinc-400 mt-0.5">Authoritative snapshot verification, RPO/RTO compliance, and backup telemetry.</p>
        </div>
        <div class="flex items-center space-x-3">
            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse mr-2"></span>
                RPO/RTO: COMPLIANT
            </span>
        </div>
    </div>

    <!-- Backup Metadata Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-4">
            <span class="text-xs font-semibold text-zinc-400 uppercase tracking-wider">Recovery Point Objective (RPO)</span>
            <div class="mt-2 text-2xl font-black text-white font-mono">{{ $backupStatus['rpo_target'] }}</div>
            <p class="text-xs text-emerald-400 mt-1 font-semibold">Continuous WAL archiving</p>
        </div>
        <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-4">
            <span class="text-xs font-semibold text-zinc-400 uppercase tracking-wider">Recovery Time Objective (RTO)</span>
            <div class="mt-2 text-2xl font-black text-white font-mono">{{ $backupStatus['rto_target'] }}</div>
            <p class="text-xs text-emerald-400 mt-1 font-semibold">Automated cluster restore</p>
        </div>
        <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-4">
            <span class="text-xs font-semibold text-zinc-400 uppercase tracking-wider">Snapshot Frequency</span>
            <div class="mt-2 text-2xl font-black text-white font-mono">{{ $backupStatus['frequency'] }}</div>
            <p class="text-xs text-zinc-400 mt-1">Multi-region replicated</p>
        </div>
        <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-4">
            <span class="text-xs font-semibold text-zinc-400 uppercase tracking-wider">Retention Policy</span>
            <div class="mt-2 text-2xl font-black text-white font-mono">30 Days</div>
            <p class="text-xs text-zinc-400 mt-1">AES-256 encrypted</p>
        </div>
    </div>

    <!-- Restore Verification Table -->
    <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-6 shadow-md">
        <h2 class="text-base font-bold text-white mb-4 flex items-center gap-2">
            <i class="fa-solid fa-shield-halved text-emerald-400"></i>
            Automated Restore Drill Verification
        </h2>
        <div class="p-4 rounded-xl border border-zinc-800 bg-zinc-950 space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-xs text-zinc-400">Last Staging Restore Verification:</span>
                <span class="text-xs font-mono text-white">{{ $backupStatus['last_restore_verification'] }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-xs text-zinc-400">Verification Outcome:</span>
                <span class="text-xs font-bold text-emerald-400">{{ $backupStatus['verification_result'] }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-xs text-zinc-400">Remote Storage Target:</span>
                <span class="text-xs font-mono text-zinc-300">{{ $backupStatus['target'] }}</span>
            </div>
        </div>
    </div>

</div>
@endsection
