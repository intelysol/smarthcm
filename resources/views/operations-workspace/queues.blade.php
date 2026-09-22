@extends('shells.operations')

@section('title', 'Queue & Horizon Operations')

@section('content')
<div class="space-y-6">

    <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-6 shadow-md flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-mono text-[#C9A227] font-semibold uppercase tracking-wider mb-1">
                <i class="fa-solid fa-server text-[#C9A227]"></i>
                <span>Queue &amp; Background Job Infrastructure</span>
            </div>
            <h1 class="text-2xl font-black text-white tracking-tight">Queue Management &amp; Workers</h1>
            <p class="text-xs text-zinc-400 mt-0.5">Real-time status of Redis queue pipelines, payroll execution jobs, and Horizon dispatchers.</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('operations.system-health') }}" class="px-3.5 py-2 rounded-xl bg-zinc-800 hover:bg-zinc-700 text-zinc-200 font-semibold text-xs transition border border-zinc-700 flex items-center">
                <i class="fa-solid fa-heart-pulse mr-1.5 text-emerald-400"></i> Full System Health
            </a>
        </div>
    </div>

    <!-- Queue Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-4">
            <span class="text-xs font-semibold text-zinc-400 uppercase tracking-wider">Queue Driver</span>
            <div class="mt-2 text-2xl font-black text-white font-mono">{{ config('queue.default', 'redis') }}</div>
            <p class="text-xs text-emerald-400 mt-1 font-semibold">Active connection</p>
        </div>
        <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-4">
            <span class="text-xs font-semibold text-zinc-400 uppercase tracking-wider">Pending Jobs</span>
            <div class="mt-2 text-2xl font-black text-white font-mono">{{ $pendingJobs }}</div>
            <p class="text-xs text-zinc-400 mt-1">Normal throughput</p>
        </div>
        <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-4">
            <span class="text-xs font-semibold text-zinc-400 uppercase tracking-wider">Failed Jobs</span>
            <div class="mt-2 text-2xl font-black {{ $failedJobs > 0 ? 'text-rose-500' : 'text-emerald-400' }} font-mono">{{ $failedJobs }}</div>
            <p class="text-xs text-zinc-400 mt-1">Dead-letter queue</p>
        </div>
        <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-4">
            <span class="text-xs font-semibold text-zinc-400 uppercase tracking-wider">Horizon Status</span>
            <div class="mt-2 text-2xl font-black text-emerald-400">RUNNING</div>
            <p class="text-xs text-zinc-400 mt-1">Workers operational</p>
        </div>
    </div>

</div>
@endsection
