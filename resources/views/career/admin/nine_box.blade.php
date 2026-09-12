@extends('layouts.career')

@section('title', 'Nine-Box Talent Matrix')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-800/80 p-6 rounded-2xl border border-slate-700">
        <div>
            <h1 class="text-2xl font-bold text-white flex items-center gap-2">
                <i class="fa-solid fa-shapes text-emerald-400"></i> Nine-Box Talent Matrix
            </h1>
            <p class="text-sm text-slate-400 mt-1">Calibration grid mapping Performance vs. Future Leadership Potential.</p>
        </div>
        <span class="px-3 py-1 rounded-lg text-xs font-semibold bg-rose-500/10 text-rose-300 border border-rose-500/20">
            <i class="fa-solid fa-lock mr-1"></i> Highly Confidential
        </span>
    </div>

    <!-- 3x3 Grid Container -->
    <div class="bg-slate-800/60 p-6 rounded-2xl border border-slate-700/80">
        <div class="grid grid-cols-3 gap-4 min-h-[500px]">
            <!-- Row 1: High Potential -->
            <!-- Cell: Low Perf / High Pot (Enigma) -->
            <div class="bg-slate-900/80 p-4 rounded-xl border border-slate-700/80 flex flex-col justify-between">
                <div>
                    <span class="text-xs font-bold text-amber-400 uppercase tracking-wider block">Enigma / Rough Diamond</span>
                    <span class="text-xs text-slate-500">Low Perf &bull; High Pot</span>
                </div>
                <div class="text-right">
                    <span class="text-2xl font-bold text-slate-200">
                        {{ $records->where('nine_box_position', 'low_performance_high_potential')->count() }}
                    </span>
                </div>
            </div>

            <!-- Cell: Med Perf / High Pot (Growth Star) -->
            <div class="bg-emerald-950/30 p-4 rounded-xl border border-emerald-500/30 flex flex-col justify-between">
                <div>
                    <span class="text-xs font-bold text-emerald-400 uppercase tracking-wider block">Growth Star</span>
                    <span class="text-xs text-slate-500">Med Perf &bull; High Pot</span>
                </div>
                <div class="text-right">
                    <span class="text-2xl font-bold text-emerald-300">
                        {{ $records->where('nine_box_position', 'medium_performance_high_potential')->count() }}
                    </span>
                </div>
            </div>

            <!-- Cell: High Perf / High Pot (Top Talent / Star) -->
            <div class="bg-emerald-900/40 p-4 rounded-xl border border-emerald-400/50 shadow-lg shadow-emerald-900/20 flex flex-col justify-between">
                <div>
                    <span class="text-xs font-bold text-emerald-300 uppercase tracking-wider block">Star / Top Talent</span>
                    <span class="text-xs text-emerald-500">High Perf &bull; High Pot</span>
                </div>
                <div class="text-right">
                    <span class="text-3xl font-extrabold text-emerald-200">
                        {{ $records->where('nine_box_position', 'high_performance_high_potential')->count() }}
                    </span>
                </div>
            </div>

            <!-- Row 2: Medium Potential -->
            <!-- Cell: Low Perf / Med Pot (Dilemma) -->
            <div class="bg-slate-900/80 p-4 rounded-xl border border-slate-700/80 flex flex-col justify-between">
                <div>
                    <span class="text-xs font-bold text-amber-500 uppercase tracking-wider block">Dilemma</span>
                    <span class="text-xs text-slate-500">Low Perf &bull; Med Pot</span>
                </div>
                <div class="text-right">
                    <span class="text-2xl font-bold text-slate-200">
                        {{ $records->where('nine_box_position', 'low_performance_medium_potential')->count() }}
                    </span>
                </div>
            </div>

            <!-- Cell: Med Perf / Med Pot (Core Player) -->
            <div class="bg-slate-900/80 p-4 rounded-xl border border-slate-700/80 flex flex-col justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-300 uppercase tracking-wider block">Core Player</span>
                    <span class="text-xs text-slate-500">Med Perf &bull; Med Pot</span>
                </div>
                <div class="text-right">
                    <span class="text-2xl font-bold text-slate-200">
                        {{ $records->where('nine_box_position', 'medium_performance_medium_potential')->count() }}
                    </span>
                </div>
            </div>

            <!-- Cell: High Perf / Med Pot (High Performer) -->
            <div class="bg-emerald-950/30 p-4 rounded-xl border border-emerald-500/30 flex flex-col justify-between">
                <div>
                    <span class="text-xs font-bold text-emerald-400 uppercase tracking-wider block">High Performer</span>
                    <span class="text-xs text-slate-500">High Perf &bull; Med Pot</span>
                </div>
                <div class="text-right">
                    <span class="text-2xl font-bold text-emerald-300">
                        {{ $records->where('nine_box_position', 'high_performance_medium_potential')->count() }}
                    </span>
                </div>
            </div>

            <!-- Row 3: Low Potential -->
            <!-- Cell: Low Perf / Low Pot (Risk) -->
            <div class="bg-rose-950/30 p-4 rounded-xl border border-rose-500/30 flex flex-col justify-between">
                <div>
                    <span class="text-xs font-bold text-rose-400 uppercase tracking-wider block">Risk / Action Needed</span>
                    <span class="text-xs text-rose-500">Low Perf &bull; Low Pot</span>
                </div>
                <div class="text-right">
                    <span class="text-2xl font-bold text-rose-300">
                        {{ $records->where('nine_box_position', 'low_performance_low_potential')->count() }}
                    </span>
                </div>
            </div>

            <!-- Cell: Med Perf / Low Pot (Effective Pro) -->
            <div class="bg-slate-900/80 p-4 rounded-xl border border-slate-700/80 flex flex-col justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-300 uppercase tracking-wider block">Effective Professional</span>
                    <span class="text-xs text-slate-500">Med Perf &bull; Low Pot</span>
                </div>
                <div class="text-right">
                    <span class="text-2xl font-bold text-slate-200">
                        {{ $records->where('nine_box_position', 'medium_performance_low_potential')->count() }}
                    </span>
                </div>
            </div>

            <!-- Cell: High Perf / Low Pot (Subject Expert) -->
            <div class="bg-teal-950/30 p-4 rounded-xl border border-teal-500/30 flex flex-col justify-between">
                <div>
                    <span class="text-xs font-bold text-teal-400 uppercase tracking-wider block">Subject Expert</span>
                    <span class="text-xs text-slate-500">High Perf &bull; Low Pot</span>
                </div>
                <div class="text-right">
                    <span class="text-2xl font-bold text-teal-300">
                        {{ $records->where('nine_box_position', 'high_performance_low_potential')->count() }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
