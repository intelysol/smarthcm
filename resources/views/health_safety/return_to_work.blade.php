@extends('health_safety.layout')

@section('title', 'Return to Work (RTW) & Disability Coordination')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-white">Return to Work Coordination</h1>
            <p class="mt-1 text-sm text-slate-400">Manage phased employee transitions following occupational illness or injury leave.</p>
        </div>
        <div>
            <button type="button" class="inline-flex items-center justify-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500 transition">
                + Initiate RTW Case
            </button>
        </div>
    </div>

    <!-- Active Cases Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="rounded-xl border border-slate-800 bg-slate-950/40 p-5 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <span class="text-xs font-mono text-emerald-400">RTW-202609-0012</span>
                <span class="inline-flex rounded-full bg-purple-500/10 px-2 py-0.5 text-xs font-medium text-purple-400 ring-1 ring-inset ring-purple-500/20">
                    Phased Return
                </span>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-white">Production Line Technician</h3>
                <p class="text-xs text-slate-400">Manufacturing Plant 2</p>
            </div>
            <div class="rounded-lg bg-slate-900/60 p-3 text-xs space-y-1.5 border border-slate-800">
                <div class="flex justify-between text-slate-400">
                    <span>Target Return:</span>
                    <span class="font-medium text-slate-200">2026-09-20</span>
                </div>
                <div class="flex justify-between text-slate-400">
                    <span>Current Phase:</span>
                    <span class="font-medium text-emerald-400">Phase 2 (24 hrs/wk)</span>
                </div>
                <div class="flex justify-between text-slate-400">
                    <span>Accommodation:</span>
                    <span class="font-medium text-slate-200">Anti-fatigue station</span>
                </div>
            </div>
            <button type="button" class="w-full rounded-md bg-slate-800 py-1.5 px-3 text-xs font-semibold text-slate-200 hover:bg-slate-700 transition">
                View Progression
            </button>
        </div>
    </div>
</div>
@endsection
