@extends('layouts.attendance')

@section('title', 'Work & Holiday Calendars')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Work Calendars & Holiday Schedules</h1>
            <p class="text-sm text-slate-400 mt-1">Multi-entity weekly working day patterns and annual holiday schedules.</p>
        </div>
        <div class="flex items-center gap-3">
            <button class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-medium text-sm transition flex items-center gap-2">
                <i class="fa-solid fa-plus"></i> New Work Calendar
            </button>
            <button class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 font-medium text-sm transition flex items-center gap-2">
                <i class="fa-solid fa-calendar-plus"></i> Add Holiday Calendar
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Work Calendar -->
        <div class="bg-slate-800/80 rounded-2xl p-6 border border-slate-700/60 shadow-xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-700/60 pb-3">
                <div>
                    <h2 class="font-bold text-white">Standard 5-Day Work Calendar</h2>
                    <span class="text-xs text-slate-400">STD-5DAY (UTC)</span>
                </div>
                <span class="px-2.5 py-1 rounded-full bg-emerald-500/20 text-emerald-300 text-xs font-semibold">Default</span>
            </div>
            <div class="space-y-2">
                <div class="flex justify-between text-xs py-1 border-b border-slate-700/40 text-slate-300">
                    <span>Monday &mdash; Friday</span>
                    <span class="text-emerald-400 font-medium">Working Day (8 hrs)</span>
                </div>
                <div class="flex justify-between text-xs py-1 text-slate-400">
                    <span>Saturday &mdash; Sunday</span>
                    <span class="text-rose-400 font-medium">Weekly Rest Days</span>
                </div>
            </div>
        </div>

        <!-- Holiday Calendar -->
        <div class="bg-slate-800/80 rounded-2xl p-6 border border-slate-700/60 shadow-xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-700/60 pb-3">
                <div>
                    <h2 class="font-bold text-white">Corporate Holidays 2026</h2>
                    <span class="text-xs text-slate-400">HOL-2026 (Annual)</span>
                </div>
                <span class="px-2.5 py-1 rounded-full bg-indigo-500/20 text-indigo-300 text-xs font-semibold">Active</span>
            </div>
            <div class="space-y-2 text-xs text-slate-300">
                <div class="flex justify-between py-1 border-b border-slate-700/40">
                    <span>2026-01-01 &mdash; New Year's Day</span>
                    <span class="text-indigo-400">Public</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-700/40">
                    <span>2026-05-01 &mdash; Labor Day</span>
                    <span class="text-indigo-400">Public</span>
                </div>
                <div class="flex justify-between py-1">
                    <span>2026-08-14 &mdash; Independence Day</span>
                    <span class="text-indigo-400">National</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
