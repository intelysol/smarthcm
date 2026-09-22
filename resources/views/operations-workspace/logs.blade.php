@extends('shells.operations')

@section('title', 'Operational Logs & Telemetry')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-white">Operational Logs &amp; Diagnostics</h1>
            <p class="text-xs text-zinc-400">System audit trails, integration job outcomes, and runtime logs</p>
        </div>
        <a href="{{ route('operations.system-health') }}" class="text-xs font-semibold text-[#C9A227] hover:underline">&larr; Back to Health Monitor</a>
    </div>

    <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-5">
        <h2 class="text-xs font-bold text-zinc-400 uppercase tracking-wider mb-3">Recent Operational Events</h2>
        <div class="font-mono text-xs text-zinc-300 space-y-2">
            <div class="p-2.5 bg-black/40 rounded border border-zinc-800 flex items-center justify-between">
                <span>[INFO] Biometric attendance sync cycle completed successfully (14 devices).</span>
                <span class="text-zinc-500 text-[11px]">2 mins ago</span>
            </div>
            <div class="p-2.5 bg-black/40 rounded border border-zinc-800 flex items-center justify-between">
                <span>[INFO] Automated payroll accrual snapshot generated for current month.</span>
                <span class="text-zinc-500 text-[11px]">15 mins ago</span>
            </div>
            <div class="p-2.5 bg-black/40 rounded border border-zinc-800 flex items-center justify-between">
                <span>[INFO] Redis queue heartbeat OK &bull; Horizon 12 active workers balanced.</span>
                <span class="text-emerald-400 text-[11px]">Active</span>
            </div>
        </div>
    </div>
</div>
@endsection
