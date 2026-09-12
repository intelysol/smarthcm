@extends('layouts.career')

@section('title', 'Mentoring & Coaching')

@section('content')
<div class="space-y-6">
    <div class="bg-slate-800/80 p-6 rounded-2xl border border-slate-700">
        <h1 class="text-2xl font-bold text-white flex items-center gap-2">
            <i class="fa-solid fa-handshake-angle text-emerald-400"></i> Mentoring & Coaching Hub
        </h1>
        <p class="text-sm text-slate-400 mt-1">Connect with mentors, set strategic growth goals, and accelerate your leadership journey.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-slate-800/60 p-6 rounded-2xl border border-slate-700/80 space-y-4">
            <h2 class="text-lg font-bold text-white flex items-center gap-2">
                <i class="fa-solid fa-user-tie text-emerald-400"></i> My Active Mentorships
            </h2>
            <div class="p-8 text-center bg-slate-900/40 rounded-xl border border-slate-800">
                <i class="fa-solid fa-people-arrows text-slate-600 text-3xl mb-2"></i>
                <p class="text-slate-300 text-sm font-medium">No active mentoring relationship.</p>
                <p class="text-xs text-slate-400 mt-1">Explore available programs to request a mentor.</p>
            </div>
        </div>

        <div class="bg-slate-800/60 p-6 rounded-2xl border border-slate-700/80 space-y-4">
            <h2 class="text-lg font-bold text-white flex items-center gap-2">
                <i class="fa-solid fa-bullseye text-teal-400"></i> Mentoring Objectives & Goals
            </h2>
            <div class="p-8 text-center bg-slate-900/40 rounded-xl border border-slate-800">
                <i class="fa-solid fa-flag-checkered text-slate-600 text-3xl mb-2"></i>
                <p class="text-slate-300 text-sm font-medium">Goals will appear when a mentorship is active.</p>
            </div>
        </div>
    </div>
</div>
@endsection
