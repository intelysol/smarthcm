@extends('layouts.attendance')

@section('title', 'Shift & Pattern Configuration')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Shift Definitions & Rotation Patterns</h1>
            <p class="text-sm text-slate-400 mt-1">Configure standard, overnight, flexible, and split shifts with tolerance windows and breaks.</p>
        </div>
        <button class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-medium text-sm transition flex items-center gap-2">
            <i class="fa-solid fa-plus"></i> Create Shift
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        <!-- Morning Shift Card -->
        <div class="bg-slate-800/80 rounded-2xl p-5 border border-slate-700/60 shadow-xl space-y-4">
            <div class="flex items-center justify-between">
                <span class="px-2.5 py-1 rounded-lg bg-indigo-500/20 text-indigo-300 font-semibold text-xs border border-indigo-500/30">SHIFT-MORN</span>
                <span class="text-xs text-slate-400 font-mono">08:00 &mdash; 17:00</span>
            </div>
            <div>
                <h2 class="text-lg font-bold text-white">Standard Morning Shift</h2>
                <p class="text-xs text-slate-400 mt-1">8 hours standard duty + 60m lunch break. 15m grace period.</p>
            </div>
            <div class="pt-3 border-t border-slate-700/60 flex items-center justify-between text-xs text-slate-400">
                <span>Grace: 15 mins</span>
                <span class="text-emerald-400 font-medium">Overtime Eligible</span>
            </div>
        </div>

        <!-- Night Shift Card -->
        <div class="bg-slate-800/80 rounded-2xl p-5 border border-slate-700/60 shadow-xl space-y-4">
            <div class="flex items-center justify-between">
                <span class="px-2.5 py-1 rounded-lg bg-purple-500/20 text-purple-300 font-semibold text-xs border border-purple-500/30">SHIFT-NIGHT</span>
                <span class="text-xs text-slate-400 font-mono">22:00 &mdash; 06:00 (+1)</span>
            </div>
            <div>
                <h2 class="text-lg font-bold text-white">Overnight Shift</h2>
                <p class="text-xs text-slate-400 mt-1">Cross-midnight nocturnal rotation with overnight window pairing.</p>
            </div>
            <div class="pt-3 border-t border-slate-700/60 flex items-center justify-between text-xs text-slate-400">
                <span>Overnight: Yes</span>
                <span class="text-purple-400 font-medium">Night Differential</span>
            </div>
        </div>

        <!-- Flexible Shift Card -->
        <div class="bg-slate-800/80 rounded-2xl p-5 border border-slate-700/60 shadow-xl space-y-4">
            <div class="flex items-center justify-between">
                <span class="px-2.5 py-1 rounded-lg bg-emerald-500/20 text-emerald-300 font-semibold text-xs border border-emerald-500/30">SHIFT-FLEX</span>
                <span class="text-xs text-slate-400 font-mono">07:00 &mdash; 19:00</span>
            </div>
            <div>
                <h2 class="text-lg font-bold text-white">Flexible Hours Shift</h2>
                <p class="text-xs text-slate-400 mt-1">Core hours presence 10:00 &mdash; 15:00. 480 required daily minutes.</p>
            </div>
            <div class="pt-3 border-t border-slate-700/60 flex items-center justify-between text-xs text-slate-400">
                <span>Core: 10:00 - 15:00</span>
                <span class="text-emerald-400 font-medium">Flexible</span>
            </div>
        </div>
    </div>
</div>
@endsection
