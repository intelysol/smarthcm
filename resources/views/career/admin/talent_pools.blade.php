@extends('layouts.career')

@section('title', 'Talent Pools')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-800/80 p-6 rounded-2xl border border-slate-700">
        <div>
            <h1 class="text-2xl font-bold text-white flex items-center gap-2">
                <i class="fa-solid fa-layer-group text-emerald-400"></i> Strategic Talent Pools
            </h1>
            <p class="text-sm text-slate-400 mt-1">High-potential, critical skills, and executive leadership cohorts.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($pools as $pool)
        <div class="bg-slate-800/60 p-5 rounded-2xl border border-slate-700/80 hover:border-emerald-500/40 transition flex flex-col justify-between">
            <div>
                <div class="flex justify-between items-start">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-emerald-400">{{ $pool->code }}</span>
                        <h3 class="text-lg font-bold text-white mt-0.5">{{ $pool->name }}</h3>
                    </div>
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                        {{ $pool->members->count() }} Members
                    </span>
                </div>
                <p class="text-xs text-slate-400 mt-2">{{ $pool->description ?? 'Strategic cohort tracking.' }}</p>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-700/60 flex justify-between items-center text-xs">
                <span class="text-slate-500">Status: {{ ucwords($pool->status) }}</span>
                <span class="text-emerald-400 font-semibold hover:underline cursor-pointer">Manage Cohort &rarr;</span>
            </div>
        </div>
        @empty
        <div class="col-span-full bg-slate-800/40 p-12 text-center rounded-2xl border border-slate-700">
            <p class="text-slate-400 text-sm">No talent pools created yet.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
