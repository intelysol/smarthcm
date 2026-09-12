@extends('layouts.attendance')

@section('title', 'Roster & Workforce Scheduling Board')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Roster Planning Board</h1>
            <p class="text-sm text-slate-400 mt-1">Interactive shift assignment, conflict detection, and schedule publishing engine.</p>
        </div>
        <div class="flex items-center gap-3">
            <button class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-medium text-sm transition flex items-center gap-2">
                <i class="fa-solid fa-plus"></i> New Roster Period
            </button>
            <button class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-medium text-sm transition flex items-center gap-2">
                <i class="fa-solid fa-paper-plane"></i> Publish Roster
            </button>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-slate-800/80 rounded-2xl p-4 border border-slate-700/60 flex flex-wrap gap-4 items-center justify-between">
        <div class="flex items-center gap-3">
            <select class="bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-200">
                <option>All Departments</option>
            </select>
            <select class="bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-200">
                <option>All Locations</option>
            </select>
        </div>
        <div class="flex items-center gap-2">
            <span class="w-3 h-3 rounded-full bg-indigo-500"></span> <span class="text-xs text-slate-400 mr-2">Morning Shift</span>
            <span class="w-3 h-3 rounded-full bg-cyan-500"></span> <span class="text-xs text-slate-400 mr-2">Evening Shift</span>
            <span class="w-3 h-3 rounded-full bg-purple-500"></span> <span class="text-xs text-slate-400 mr-2">Night Shift</span>
            <span class="w-3 h-3 rounded-full bg-emerald-500"></span> <span class="text-xs text-slate-400">Flexible</span>
        </div>
    </div>

    <!-- Interactive Grid Mockup / Table -->
    <div class="bg-slate-800/80 rounded-2xl border border-slate-700/60 overflow-hidden shadow-xl">
        <div class="p-4 border-b border-slate-700/60 flex items-center justify-between">
            <h2 class="font-semibold text-white">Current Period: Sept 01 - Sept 30, 2026</h2>
            <span class="px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300 text-xs font-semibold border border-emerald-500/30">Active Roster</span>
        </div>
        <div class="p-8 text-center text-slate-400">
            <i class="fa-solid fa-calendar-days text-4xl text-slate-600 mb-3"></i>
            <p class="text-sm">Interactive Roster Grid loaded via API. Use REST endpoints to manage drag-and-drop assignments.</p>
        </div>
    </div>
</div>
@endsection
