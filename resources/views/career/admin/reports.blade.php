@extends('layouts.career')

@section('title', 'Talent Analytics & Reports')

@section('content')
<div class="space-y-6">
    <div class="bg-slate-800/80 p-6 rounded-2xl border border-slate-700">
        <h1 class="text-2xl font-bold text-white flex items-center gap-2">
            <i class="fa-solid fa-chart-pie text-emerald-400"></i> Talent & Succession Intelligence
        </h1>
        <p class="text-sm text-slate-400 mt-1">Aggregated analytics, metrics, and standard reports.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="bg-slate-800/60 p-5 rounded-2xl border border-slate-700/80 space-y-3">
            <h3 class="text-base font-bold text-white flex items-center gap-2">
                <i class="fa-solid fa-file-lines text-emerald-400"></i> Skill Inventory Report
            </h3>
            <p class="text-xs text-slate-400">Complete workforce skill distribution and verified proficiency records.</p>
            <button class="w-full text-xs font-semibold py-2 rounded-lg bg-slate-900 hover:bg-slate-800 text-emerald-400 border border-slate-700 transition">View Report &rarr;</button>
        </div>

        <div class="bg-slate-800/60 p-5 rounded-2xl border border-slate-700/80 space-y-3">
            <h3 class="text-base font-bold text-white flex items-center gap-2">
                <i class="fa-solid fa-triangle-exclamation text-amber-400"></i> Skill Gap Analysis
            </h3>
            <p class="text-xs text-slate-400">Identify critical capability deficiencies against target positions.</p>
            <button class="w-full text-xs font-semibold py-2 rounded-lg bg-slate-900 hover:bg-slate-800 text-amber-400 border border-slate-700 transition">View Report &rarr;</button>
        </div>

        <div class="bg-slate-800/60 p-5 rounded-2xl border border-slate-700/80 space-y-3">
            <h3 class="text-base font-bold text-white flex items-center gap-2">
                <i class="fa-solid fa-chart-line text-teal-400"></i> Succession Coverage
            </h3>
            <p class="text-xs text-slate-400">Bench depth, vacancy risks, and ready-now successor percentages.</p>
            <button class="w-full text-xs font-semibold py-2 rounded-lg bg-slate-900 hover:bg-slate-800 text-teal-400 border border-slate-700 transition">View Report &rarr;</button>
        </div>
    </div>
</div>
@endsection
